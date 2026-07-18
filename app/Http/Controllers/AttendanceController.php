<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{Student,StudentAttendance,PaymentTransaction};

class AttendanceController extends Controller {

    // Fee-due banner threshold: days since the family's last payment
    private const FEE_DUE_DAYS = 21;

    // New attendance sheet
    public function sheet(Request $r){
        $date = $r->input('date', date('Y-m-d'));
        $reference = $r->input('reference');
        $subject = $r->input('subject');
        
        // Check if selected date is weekend (Saturday=6, Sunday=7) or Friday (5)
        $dayOfWeek = date('N', strtotime($date));
        $isWeekend = in_array($dayOfWeek, [6, 7]);
        $isFriday = ($dayOfWeek == 5);
        
        $students = Student::when($reference,function($q)use($reference){ $q->where('reference',$reference); })
                    ->orderBy('first_name')->get();
        $teachers = \App\Models\Staff::where('role','teacher')->where('status','active')->orderBy('name')->get();

        // Fee-due banner (READ-ONLY selects, independent of the Payment Reminders
        // module): warn when the loaded family's last payment is 21+ days old
        // AND money is still outstanding
        $feeDue = null;
        if ($reference && $students->count() > 0) {
            $primary = Student::where('reference', $reference)->first();
            $siblingIds = $students->pluck('id');

            $lastPayment = PaymentTransaction::leftJoin('invoices', 'payment_transactions.invoice_id', '=', 'invoices.id')
                ->whereIn('invoices.student_id', $siblingIds)
                ->orderBy('payment_transactions.paid_on', 'desc')
                ->first();
            $daysSince = $lastPayment ? (int) abs(now()->diffInDays($lastPayment->paid_on)) : null;

            if ($lastPayment === null || $daysSince >= self::FEE_DUE_DAYS) {
                $totalBookPrice = DB::table('books')
                    ->leftJoin('students', 'books.student_reference', '=', 'students.id')
                    ->where('students.reference', $reference)
                    ->sum('books.price');
                $totalPaid = PaymentTransaction::leftJoin('invoices', 'payment_transactions.invoice_id', '=', 'invoices.id')
                    ->whereIn('invoices.student_id', $siblingIds)
                    ->sum('payment_transactions.amount');
                $outstanding = max(0, ($primary->pending_amount ?? $primary->payment ?? 0) + $totalBookPrice - $totalPaid);

                if ($outstanding > 0) {
                    $feeDue = [
                        'days'       => $daysSince,
                        'never_paid' => $lastPayment === null,
                        'amount'     => $outstanding,
                    ];
                }
            }
        }

        return view('attendance.sheet', compact('students','date','reference','subject','isWeekend','isFriday','teachers','feeDue'));
    }

    // Save attendance for many students
    public function save(Request $r){
        $data = $r->validate([
            'date'=>'required|date',
            'subject'=>'nullable|string',
            'teacher'=>'nullable|string',
            'statuses'=>'array'  // [student_id => 'present'|'absent'|'']
        ]);
        $time = $r->input('time');
        
        $duplicates = [];
        $saved = 0;
        
        foreach(($data['statuses'] ?? []) as $studentId => $status){
            // Skip if status is empty (Clear option selected)
            if (empty($status)) {
                continue;
            }
            
            // Check if attendance already exists for this student on this date+time
            // (Student cannot be in two places at the same time)
            $existingTime = StudentAttendance::where('student_id', $studentId)
                ->where('date', $data['date'])
                ->where('time', $time)
                ->first();
            
            $student = \App\Models\Student::find($studentId);
            $studentName = $student ? $student->first_name . ' ' . $student->last_name : 'Unknown';
            
            if ($existingTime) {
                // Student already has attendance for this time slot
                $duplicates[] = $studentName . ' (already logged at ' . $time . ')';
                continue;
            }

            // The database allows only ONE record per student + date + subject
            // (unique index) — a save for the same subject at a DIFFERENT time
            // slot would still collide, so skip it gracefully instead of crashing
            if (!empty($data['subject'])) {
                $existingSubject = StudentAttendance::where('student_id', $studentId)
                    ->where('date', $data['date'])
                    ->where('subject', $data['subject'])
                    ->first();
                if ($existingSubject) {
                    $duplicates[] = $studentName . ' (already marked for ' . $data['subject'] . ' on this date)';
                    continue;
                }
            }

            // Save new attendance
            StudentAttendance::create([
                'student_id' => $studentId,
                'date' => $data['date'],
                'subject' => $data['subject'],
                'teacher' => $data['teacher'] ?? null,
                'time' => $time,
                'status' => $status
            ]);
            $saved++;
        }
        
        if (count($duplicates) > 0) {
            $message = "Attendance saved for $saved student(s). Skipped duplicates: " . implode(', ', $duplicates);
            return back()->with('warning', $message);
        }
        
        return back()->with('ok', 'Attendance saved successfully');
    }

    // View attendance (students / teacher)
    public function view(Request $r){
        $from=$r->input('from'); $to=$r->input('to'); $studentId=$r->input('student_id'); $reference=$r->input('reference'); $teacher=$r->input('teacher');
        $mode = $r->input('mode'); // null | present | absent
        
        // Statistics parameters
        $statsDate = $r->input('stats_date');
        $statsTime = $r->input('stats_time');
        
        // Get available dates with attendance records (last 60 days)
        $availableDates = StudentAttendance::selectRaw('DISTINCT date')
            ->where('date', '>=', date('Y-m-d', strtotime('-60 days')))
            ->orderBy('date', 'desc')
            ->pluck('date')
            ->toArray();
        
        // Don't auto-select date - let user choose (shows all records by default)
        // $statsDate comes from request or remains null
        
        // Determine day type for time slots
        $dayType = 'weekday';
        $timeSlots = [];
        
        // If date is selected, use that date's day type
        // If no date selected, default to current day's slots for display
        $dateForSlots = $statsDate ?: date('Y-m-d');
        $dayOfWeek = date('N', strtotime($dateForSlots));
        $isWeekend = in_array($dayOfWeek, [6, 7]);
        $isFriday = ($dayOfWeek == 5);
        
        if ($isWeekend) {
            $dayType = 'weekend';
            $timeSlots = [
                '9:00 AM — 11:00 AM',
                '11:15 AM — 1:15 PM',
                '2:15 PM — 4:15 PM',
                '4:30 PM — 6:30 PM'
            ];
        } elseif ($isFriday) {
            $dayType = 'friday';
            $timeSlots = [
                '9:00 AM — 11:00 AM',
                '11:15 AM — 1:15 PM',
                '4:45 PM — 6:45 PM',
                '7:00 PM — 9:00 PM'
            ];
        } else {
            $dayType = 'weekday';
            $timeSlots = [
                '12:00 PM — 2:00 PM',
                '2:15 PM — 4:15 PM',
                '4:45 PM — 6:45 PM',
                '7:00 PM — 9:00 PM'
            ];
        }
        
        // Calculate present count
        $presentCount = 0;
        if ($statsDate) {
            // If date is selected, count for that date
            $statsQuery = StudentAttendance::where('date', $statsDate)
                ->where('status', 'present');
            if ($statsTime) {
                $statsQuery->where('time', $statsTime);
            }
            $presentCount = $statsQuery->count();
        } elseif ($statsTime) {
            // If only time slot selected (no date), count across all dates for that time
            $presentCount = StudentAttendance::where('time', $statsTime)
                ->where('status', 'present')
                ->count();
        }
        
        // Main table query
        $q = StudentAttendance::query()->with('student');
        
        // Stats filters take priority (Option A: stats_date overrides from/to)
        if($statsDate) {
            $q->where('date', $statsDate);
        } else {
            // Only apply from/to if stats_date is not set
            if($from) $q->where('date','>=',$from);
            if($to)   $q->where('date','<=',$to);
        }
        
        // Add time slot filter if selected
        if($statsTime) $q->where('time', $statsTime);
        
        // Apply other filters
        if($studentId) $q->where('student_id',$studentId);
        if($reference) $q->whereHas('student', fn($qq)=>$qq->where('reference',$reference));
        if($teacher) $q->where('teacher','like',"%$teacher%");
        if($mode === 'present') $q->where('status','present');

        $rows = $q->orderBy('date','desc')->limit(500)->get();
        $students = \App\Models\Student::orderBy('first_name')->get();
        $teachers = \App\Models\Staff::where('role','teacher')->where('status','active')->orderBy('name')->get();

        // Union of weekday/Friday/weekend slots, for the edit modal's time dropdown
        $allTimeSlots = [
            '9:00 AM — 11:00 AM',
            '11:15 AM — 1:15 PM',
            '12:00 PM — 2:00 PM',
            '2:15 PM — 4:15 PM',
            '4:30 PM — 6:30 PM',
            '4:45 PM — 6:45 PM',
            '7:00 PM — 9:00 PM',
        ];

        // Absent mode: students scheduled by their admission timetable but with NO
        // attendance record marked. Dates: stats_date wins; else the From/To range
        // (future days skipped, capped at the most recent 31 days); else today.
        $absentRows = collect();
        $absentDate = null;   // set for single-day views; null when a range is shown
        $absentFrom = null;
        $absentTo = null;
        if ($mode === 'absent') {
            $today = date('Y-m-d');
            if ($statsDate) {
                $targetDates = [$statsDate];
                $absentDate = $statsDate;
            } elseif ($from || $to) {
                $start = $from ?: $to;
                $end = ($to ?: $today);
                if ($end > $today) $end = $today;
                $absentFrom = $start;
                $absentTo = $end;
                $targetDates = [];
                if ($start <= $end) {
                    $startTs = strtotime($start);
                    $endTs = strtotime($end);
                    if ((($endTs - $startTs) / 86400) + 1 > 31) {
                        $startTs = strtotime('-30 days', $endTs); // cap: most recent 31 days
                    }
                    for ($ts = $endTs; $ts >= $startTs; $ts -= 86400) {
                        $targetDates[] = date('Y-m-d', $ts);
                    }
                }
            } else {
                $targetDates = [$today];
                $absentDate = $today;
            }

            // start_time (HH:MM) -> attendance slot label (bijective, day-independent)
            $startToLabel = [
                '09:00' => '9:00 AM — 11:00 AM',
                '11:15' => '11:15 AM — 1:15 PM',
                '12:00' => '12:00 PM — 2:00 PM',
                '14:15' => '2:15 PM — 4:15 PM',
                '16:30' => '4:30 PM — 6:30 PM',
                '16:45' => '4:45 PM — 6:45 PM',
                '19:00' => '7:00 PM — 9:00 PM',
            ];

            $scheduledByDay = []; // memoized per weekday (0=Mon..6=Sun)

            foreach ($targetDates as $targetDate) {
                $tday = ((int) date('N', strtotime($targetDate)) + 6) % 7;

                if (!array_key_exists($tday, $scheduledByDay)) {
                    // Monthly students have 4 week rows per slot — dedupe by student + start time
                    $sched = \App\Models\Timetable::where('day_of_week', (string) $tday)->get()
                        ->unique(fn($t) => $t->student_id . '|' . substr((string) $t->start_time, 0, 5))
                        ->values();
                    if ($statsTime) {
                        $sched = $sched->filter(fn($t) => ($startToLabel[substr((string) $t->start_time, 0, 5)] ?? null) === $statsTime)->values();
                    }
                    if ($reference) $sched = $sched->where('student_reference', $reference)->values();
                    if ($studentId) $sched = $sched->where('student_id', $studentId)->values();
                    $scheduledByDay[$tday] = $sched;
                }
                $scheduled = $scheduledByDay[$tday];
                if ($scheduled->isEmpty()) continue;

                $markedByStudent = StudentAttendance::where('date', $targetDate)
                    ->get(['student_id', 'time'])
                    ->groupBy('student_id');

                foreach ($scheduled as $t) {
                    $start = substr((string) $t->start_time, 0, 5);
                    $slotLabel = $startToLabel[$start] ?? ($start . ' — ' . substr((string) $t->end_time, 0, 5));

                    // Covered if marked for this slot label, or a day-level record without a time (any status)
                    $recs = $markedByStudent->get($t->student_id, collect());
                    if ($recs->contains(fn($a) => $a->time === $slotLabel || empty($a->time))) continue;

                    $absentRows->push((object) [
                        'date'       => $targetDate,
                        'start'      => $start,
                        'student_id' => $t->student_id,
                        'slot'       => $slotLabel,
                        'subject'    => $t->subject,
                    ]);
                }
            }

            // Attach students in one query; newest date first, then slot, then name
            $studentsById = Student::whereIn('id', $absentRows->pluck('student_id')->unique())->get()->keyBy('id');
            $absentRows = $absentRows
                ->map(function ($a) use ($studentsById) {
                    $stu = $studentsById->get($a->student_id);
                    if (!$stu) return null;
                    $a->student = $stu;
                    $a->reference = $stu->reference;
                    return $a;
                })
                ->filter()
                ->sort(fn($x, $y) => ($y->date <=> $x->date) ?: ($x->start <=> $y->start) ?: strcmp($x->student->first_name, $y->student->first_name))
                ->values();
        }

        return view('attendance.view', compact('rows','students','teachers','from','to','studentId','reference','teacher','availableDates','statsDate','statsTime','timeSlots','presentCount','dayType','allTimeSlots','mode','absentRows','absentDate','absentFrom','absentTo'));
    }

    // Class status by date (Attendance Data)
    public function statusByDate(Request $r){
        $date = $r->input('date', date('Y-m-d'));
        $rows = StudentAttendance::with('student')->where('date',$date)->get()->keyBy('student_id');
        $students = Student::orderBy('first_name')->get();
        return view('attendance.status', compact('date','rows','students'));
    }

    // Update attendance record (edited from the view page)
    public function update(Request $r, StudentAttendance $attendance)
    {
        $data = $r->validate([
            'date'    => 'required|date',
            'status'  => 'required|in:present,absent',
            'subject' => 'nullable|string|max:100',
            'time'    => 'nullable|string|max:50',
            'teacher' => 'nullable|string|max:100',
        ]);

        // Guard the unique(student_id, date, subject) index: the same student
        // cannot have two records for one date + subject
        $subject = $data['subject'] ?? null;
        $dup = StudentAttendance::where('student_id', $attendance->student_id)
            ->where('date', $data['date'])
            ->where('id', '!=', $attendance->id);
        if ($subject !== null && $subject !== '') {
            $dup->where('subject', $subject);
        } else {
            $dup->whereNull('subject');
        }
        if ($dup->exists()) {
            return back()->with('warning', 'Not saved: this student already has an attendance record for that date and subject.');
        }

        $attendance->update($data);

        return back()->with('ok', 'Attendance record updated');
    }

    // Delete attendance record
    public function destroy(StudentAttendance $attendance)
    {
        $attendance->delete();
        return back()->with('ok', 'Attendance record deleted successfully');
    }
}
