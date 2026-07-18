<?php
namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Staff;
use App\Models\StudentAttendance;
use App\Models\TeacherSalary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherSalaryController extends Controller
{
    /** Salary page: filters + calculation (when a teacher is selected) + history */
    public function index(Request $r)
    {
        $teachers = Staff::where('role', 'teacher')->where('status', 'active')->orderBy('name')->get();

        $staffId = $r->input('staff_id');
        $from    = $r->input('from');
        $to      = $r->input('to');
        $status  = $r->input('status', 'all');

        // Calculation: unpaid Present class-sessions for the selected teacher/period
        $calc = null;
        if ($staffId && ($teacher = Staff::find($staffId))) {
            $sessions = $this->unpaidSessions($teacher, $from, $to);
            $hours = $sessions->count() * 2;
            $rate = $teacher->hourly_rate;
            $calc = [
                'teacher'        => $teacher,
                'sessions'       => $sessions,
                'session_count'  => $sessions->count(),
                'hours'          => $hours,
                'rate'           => $rate,
                'gross'          => $rate !== null ? $hours * (float) $rate : null,
                'next_reference' => $this->nextReference(),
            ];
        }

        $history = TeacherSalary::query()
            ->when($staffId, fn($q) => $q->where('staff_id', $staffId))
            ->when($from, fn($q) => $q->whereDate('payment_date', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('payment_date', '<=', $to))
            ->when($status && $status !== 'all', fn($q) => $q->where('status', $status))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('finance.salaries.index', compact('teachers', 'staffId', 'from', 'to', 'status', 'calc', 'history'));
    }

    /** Pay Salary: recomputes server-side, then salary + expense + attendance stamps in one transaction */
    public function store(Request $r)
    {
        $data = $r->validate([
            'staff_id'       => 'required|integer',
            'date_from'      => 'nullable|date',
            'date_to'        => 'nullable|date|after_or_equal:date_from',
            'payment_date'   => 'required|date',
            'payment_method' => 'required|in:Cash,Card,Bank',
            'notes'          => 'nullable|string|max:255',
        ]);

        $teacher = Staff::where('role', 'teacher')->findOrFail($data['staff_id']);

        if ($teacher->hourly_rate === null) {
            return back()->with('warning', "Set {$teacher->name}'s hourly rate in the Add Teacher module before paying a salary.");
        }

        $sessions = $this->unpaidSessions($teacher, $data['date_from'] ?? null, $data['date_to'] ?? null);
        if ($sessions->isEmpty()) {
            return back()->with('warning', 'No unpaid attendance sessions found for the selected teacher and period.');
        }

        $hours = $sessions->count() * 2;
        $gross = $hours * (float) $teacher->hourly_rate;
        $recordIds = $sessions->flatMap(fn($s) => $s->record_ids);

        $salary = DB::transaction(function () use ($data, $teacher, $sessions, $hours, $gross, $recordIds) {
            $reference = $this->nextReference();
            $periodFrom = $data['date_from'] ?: $sessions->min('date');
            $periodTo = $data['date_to'] ?: $sessions->max('date');

            // Finance integration: Money Out / summaries pick this up automatically
            $expense = Expense::create([
                'expense_on' => $data['payment_date'],
                'amount'     => $gross,
                'method'     => $data['payment_method'],
                'type'       => 'expense',
                'category'   => 'Teacher Salary',
                'notes'      => "Salary {$reference} — {$teacher->name} ({$periodFrom} to {$periodTo})",
            ]);

            $salary = TeacherSalary::create([
                'reference'      => $reference,
                'staff_id'       => $teacher->id,
                'teacher_name'   => $teacher->name,
                'date_from'      => $periodFrom,
                'date_to'        => $periodTo,
                'hourly_rate'    => $teacher->hourly_rate,
                'sessions'       => $sessions->count(),
                'hours'          => $hours,
                'gross'          => $gross,
                'payment_method' => $data['payment_method'],
                'payment_date'   => $data['payment_date'],
                'notes'          => $data['notes'] ?? null,
                'status'         => 'paid',
                'expense_id'     => $expense->id,
            ]);

            // Duplicate-payment prevention: these records never re-qualify
            StudentAttendance::whereIn('id', $recordIds)->update(['salary_id' => $salary->id]);

            return $salary;
        });

        return redirect()->route('salaries.slip', $salary->id)
            ->with('ok', "Salary {$salary->reference} paid to {$salary->teacher_name} — £" . number_format($gross, 2));
    }

    /** Printable salary slip (also the View action) */
    public function show(TeacherSalary $salary)
    {
        return view('finance.salaries.slip', compact('salary'));
    }

    public function edit(TeacherSalary $salary)
    {
        return view('finance.salaries.edit', compact('salary'));
    }

    /** Edit payment date / method / reference / notes — linked expense stays in sync */
    public function update(Request $r, TeacherSalary $salary)
    {
        $data = $r->validate([
            'payment_date'   => 'required|date',
            'payment_method' => 'required|in:Cash,Card,Bank',
            'reference'      => 'required|string|max:20',
            'notes'          => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($salary, $data) {
            $salary->update($data);

            if ($salary->expense_id && ($expense = Expense::find($salary->expense_id))) {
                $expense->update([
                    'expense_on' => $data['payment_date'],
                    'method'     => $data['payment_method'],
                    'notes'      => "Salary {$salary->reference} — {$salary->teacher_name} ({$salary->date_from->format('Y-m-d')} to {$salary->date_to->format('Y-m-d')})",
                ]);
            }
        });

        return redirect()->route('salaries.index')->with('ok', "Salary {$salary->reference} updated");
    }

    /** Delete: remove linked expense, restore attendance to unpaid, delete record */
    public function destroy(TeacherSalary $salary)
    {
        DB::transaction(function () use ($salary) {
            StudentAttendance::where('salary_id', $salary->id)->update(['salary_id' => null]);
            if ($salary->expense_id) {
                Expense::where('id', $salary->expense_id)->delete();
            }
            $salary->delete();
        });

        return redirect()->route('salaries.index')
            ->with('ok', "Salary {$salary->reference} deleted — its attendance sessions are unpaid again");
    }

    /** Unpaid Present records for the teacher in range, grouped into class sessions (date + slot + subject) */
    private function unpaidSessions(Staff $teacher, $from, $to)
    {
        return StudentAttendance::where('teacher', $teacher->name)
            ->where('status', 'present')
            ->whereNull('salary_id')
            ->when($from, fn($q) => $q->whereDate('date', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('date', '<=', $to))
            ->orderBy('date')->orderBy('time')
            ->get()
            ->groupBy(fn($a) => $a->date . '|' . ($a->time ?? '') . '|' . ($a->subject ?? ''))
            ->map(fn($records) => (object) [
                'date'       => $records->first()->date,
                'time'       => $records->first()->time,
                'subject'    => $records->first()->subject,
                'students'   => $records->count(),
                'record_ids' => $records->pluck('id'),
            ])
            ->values();
    }

    /** Next SAL reference (portable — no DB-specific SQL) */
    private function nextReference(): string
    {
        $max = 0;
        foreach (TeacherSalary::pluck('reference') as $ref) {
            if (preg_match('/^SAL(\d+)$/', (string) $ref, $m)) {
                $max = max($max, (int) $m[1]);
            }
        }

        return sprintf('SAL%03d', $max + 1);
    }
}
