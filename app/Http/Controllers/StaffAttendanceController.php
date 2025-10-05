<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\{Staff,StaffAttendance};

class StaffAttendanceController extends Controller {
    public function sheet(Request $r){
        $date = $r->input('date', date('Y-m-d'));
        $staff = Staff::orderBy('name')->get();
        return view('attendance.staff_sheet', compact('date','staff'));
    }
    public function save(Request $r){
        $r->validate(['date'=>'required|date','statuses'=>'array']);
        foreach(($r->statuses ?? []) as $staffId=>$status){
            StaffAttendance::updateOrCreate(
                ['staff_id'=>$staffId,'date'=>$r->date],
                ['status'=>$status]
            );
        }
        return back()->with('ok','Staff attendance saved');
    }
}
