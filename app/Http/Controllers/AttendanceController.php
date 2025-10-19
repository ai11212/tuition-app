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
            'statuses'=>'array'  // [student_id => 'present'|'absent']
        ]);
        $time = $r->input('time');
        
        $duplicates = [];
        $saved = 0;
        
        foreach(($data['statuses'] ?? []) as $studentId => $status){
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
        $q = StudentAttendance::query()->with('student');
        if($from) $q->where('date','>=',$from);
        if($to)   $q->where('date','<=',$to);
        if($studentId) $q->where('student_id',$studentId);
        if($reference) $q->whereHas('student', fn($qq)=>$qq->where('reference',$reference));
        if($teacher) $q->where('subject','like',"%$teacher%"); // simple teacher-as-subject fallback
        $rows = $q->orderBy('date','desc')->limit(500)->get();
        $students = \App\Models\Student::orderBy('first_name')->get();
        return view('attendance.view', compact('rows','students','from','to','studentId','reference','teacher'));
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
