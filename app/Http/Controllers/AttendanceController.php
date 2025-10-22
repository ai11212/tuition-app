<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\{Student,StudentAttendance};

class AttendanceController extends Controller {

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
        return view('attendance.sheet', compact('students','date','reference','subject','isWeekend','isFriday'));
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
            
            // Check if attendance already exists for this student on this date+time (can't be in two places)
            $existingTime = StudentAttendance::where('student_id', $studentId)
                ->where('date', $data['date'])
                ->where('time', $time)
                ->first();
            
            // Check if attendance already exists for this student on this date+subject (database constraint)
            $existingSubject = StudentAttendance::where('student_id', $studentId)
                ->where('date', $data['date'])
                ->where('subject', $data['subject'])
                ->first();
            
            $student = \App\Models\Student::find($studentId);
            $studentName = $student ? $student->first_name . ' ' . $student->last_name : 'Unknown';
            
            if ($existingTime) {
                // Student already has attendance for this time slot
                $duplicates[] = $studentName . ' (already logged at ' . $time . ')';
                continue;
            }
            
            if ($existingSubject) {
                // Student already has attendance for this subject today
                $duplicates[] = $studentName . ' (already logged for ' . $data['subject'] . ' today)';
                continue;
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
        if($teacher) $q->where('subject','like',"%$teacher%"); // simple teacher-as-subject fallback
        
        $rows = $q->orderBy('date','desc')->limit(500)->get();
        $students = \App\Models\Student::orderBy('first_name')->get();
        
        return view('attendance.view', compact('rows','students','from','to','studentId','reference','teacher','availableDates','statsDate','statsTime','timeSlots','presentCount','dayType'));
    }

    // Class status by date (Attendance Data)
    public function statusByDate(Request $r){
        $date = $r->input('date', date('Y-m-d'));
        $rows = StudentAttendance::with('student')->where('date',$date)->get()->keyBy('student_id');
        $students = Student::orderBy('first_name')->get();
        return view('attendance.status', compact('date','rows','students'));
    }

    // Delete attendance record
    public function destroy(StudentAttendance $attendance)
    {
        $attendance->delete();
        return back()->with('ok', 'Attendance record deleted successfully');
    }
}
