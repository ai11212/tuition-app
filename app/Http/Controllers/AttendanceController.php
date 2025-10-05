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
        $students = Student::when($reference,function($q)use($reference){ $q->where('reference',$reference); })
                    ->orderBy('first_name')->get();
        return view('attendance.sheet', compact('students','date','reference','subject'));
    }

    // Save attendance for many students
    public function save(Request $r){
        $data = $r->validate([
            'date'=>'required|date',
            'subject'=>'nullable|string',
            'statuses'=>'array'  // [student_id => 'present'|'absent']
        ]);
        foreach(($data['statuses'] ?? []) as $studentId => $status){
            StudentAttendance::updateOrCreate(
                ['student_id'=>$studentId,'date'=>$data['date'],'subject'=>$data['subject']],
                ['status'=>$status]
            );
        }
        return back()->with('ok','Attendance saved');
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
}
