<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Support\Facades\Schema;
use App\Models\Timetable;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class StudentController extends Controller
{
    public function index()
    {
    $students = \App\Models\Student::paginate(20);
        return view('students.index', compact('students'));
    }

    public function create()
    {
        // start step 1
        return view('students.create');
    }

    public function next(Request $request)
    {
        // Step-1: validate minimal guardian + main student (no save)
        $data = $request->validate([
            // guardian FIRST (post_code belongs to guardian and saved on student row)
            'guardian_name'     => 'required|string|max:120',
            'guardian_relation' => 'nullable|string|max:120',
            'guardian_phone'    => 'nullable|string|max:120',
            'guardian_email'    => 'nullable|email|max:50',
            'guardian_address'  => 'nullable|string|max:190',
            'guardian_city'     => 'nullable|string|max:120',
            'guardian_notes'    => 'nullable|string|max:255',
            'post_code'         => 'nullable|string|max:120',

            // reference optional on step1 (we will auto-generate if empty in store)
            'reference'         => 'nullable|string|max:255',

            // primary student (no phone/email/address here as requested)
            'first_name'        => 'required|string|max:100',
            'last_name'         => 'required|string|max:100',
            'gender'            => 'nullable|in:male,female,other',
            'dob'               => 'nullable|date',
            'city'              => 'nullable|string|max:120',
            'enroll_date'       => 'nullable|date',
            'start_date'        => 'nullable|date',
            'deposit'           => 'nullable|numeric',
            'period'            => 'nullable|string|max:32',

            // siblings[] array (same minimal fields)
            'siblings'                      => 'array',
            'siblings.*.first_name'         => 'nullable|string|max:100',
            'siblings.*.last_name'          => 'nullable|string|max:100',
            'siblings.*.gender'             => 'nullable|in:male,female,other',
            'siblings.*.dob'                => 'nullable|date',
        ]);

        // trim/cap guardian post_code (multibyte safe)
        if (isset($data['post_code'])) {
            $v = trim((string) $data['post_code']);
            $data['post_code'] = function_exists('mb_substr') ? mb_substr($v, 0, 120) : substr($v, 0, 120);
            if ($data['post_code'] === '') $data['post_code'] = null;
        }

        // Drop empty sibling rows
        $data['siblings'] = collect($data['siblings'] ?? [])
            ->map(fn($row) => array_filter($row ?? [], fn($v) => $v !== null && $v !== ''))
            ->filter(fn($row) => Arr::get($row, 'first_name') || Arr::get($row, 'last_name'))
            ->values()
            ->all();

        // Stash in session and go to confirm
        session(['admission' => $data]);

        return redirect()->route('students.confirm');
    }

    public function confirm(Request $request)
    {
        $admission = $request->session()->get('admission');
        if (!$admission) {
            return redirect()->route('students.create')->with('status', 'Please fill Step 1 first.');
        }
        return view('students.confirm', ['admission' => $admission]);
    }

    public function store(Request $request)
    {
        // Pull from session; nothing is saved on step 1
        $admission = $request->session()->get('admission');
        if (!$admission) {
            return redirect()->route('students.create')->with('status', 'Session expired. Please start again.');
        }

        // Ensure reference exists (DB says NOT NULL in your logs)
        $reference = $admission['reference'] ?? null;
        if (!$reference) {
            // simple auto-ref; if your project had a custom generator, replace here
            $reference = 'A' . date('ymdHis');
        }

        // Common guardian data copied to each student row (post_code is guardian’s)
        $guardian = [
            'guardian_name'     => $admission['guardian_name'] ?? null,
            'guardian_relation' => $admission['guardian_relation'] ?? null,
            'guardian_phone'    => $admission['guardian_phone'] ?? null,
            'guardian_email'    => $admission['guardian_email'] ?? null,
            'guardian_address'  => $admission['guardian_address'] ?? null,
            'post_code'         => $admission['post_code'] ?? null,
        ];

    // Create primary student
        $main = [
            'reference'   => $reference,
            'first_name'  => $admission['first_name'] ?? null,
            'last_name'   => $admission['last_name'] ?? null,
            'gender'      => $admission['gender'] ?? null,
            'dob'         => $admission['dob'] ?? null,
            'guardian_city' => $admission['guardian_city'] ?? null,
            'enroll_date' => $admission['enroll_date'] ?? null,
            'start_date'  => $admission['start_date'] ?? null,
            'deposit'     => $admission['deposit'] ?? null,
            'period'      => $admission['period'] ?? null,
        ] + $guardian;

        $created = [];
        // Filter $main to only columns that exist in the students table on this environment
        $allowed = Schema::hasTable('students') ? Schema::getColumnListing('students') : [];
        $payload = array_intersect_key($main, array_flip($allowed));
        $created[] = Student::create($payload);

    // Timetable payload from form (timetable[studentIndex][dayIndex][slotIndex] = subject)
    $timetableInput = $request->input('timetable', []);

        // Create siblings
        foreach (($admission['siblings'] ?? []) as $sib) {
            $row = [
                'reference'  => $reference,
                'first_name' => $sib['first_name'] ?? null,
                'last_name'  => $sib['last_name'] ?? null,
                'gender'     => $sib['gender'] ?? null,
                'dob'        => $sib['dob'] ?? null,
            ] + $guardian;

            // skip fully empty names
            if (!($row['first_name'] || $row['last_name'])) continue;

            $rowPayload = array_intersect_key($row, array_flip($allowed));
            $created[] = Student::create($rowPayload);
        }

        // Persist timetables: for each created student (primary + siblings)
        // Map created students 0..n-1 to timetableInput indexes
        foreach ($created as $idx => $stu) {
            $studentRef = $stu->reference;
            $studentTimetable = $timetableInput[$idx] ?? [];
            // studentTimetable: dayIndex=>[slotIndex=>subject]
            foreach ($studentTimetable as $dayIndex => $slots) {
                foreach ($slots as $slotIndex => $subject) {
                    if (!$subject) continue;
                    // Map slotIndex to times
                    $slotMap = [1=>['start'=>'12:00','end'=>'14:00'],2=>['start'=>'14:15','end'=>'16:15'],3=>['start'=>'16:45','end'=>'18:45'],4=>['start'=>'19:00','end'=>'21:00']];
                    $s = $slotMap[$slotIndex] ?? null;
                    if (!$s) continue;
                    Timetable::create([
                        'student_reference' => $studentRef,
                        'day_of_week' => (int)$dayIndex,
                        'start_time' => $s['start'],
                        'end_time' => $s['end'],
                        'subject' => $subject,
                        'teacher_name' => null,
                        'room' => null,
                    ]);
                }
            }
        }

        // Clear session & finish
        $request->session()->forget('admission');

        return redirect()->route('students.create')->with('status', 'Admission saved: '.count($created).' record(s) created. Ref '.$reference);
    }
}
