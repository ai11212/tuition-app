<?php
namespace App\Http\Controllers;
use App\Models\Timetable;
use Illuminate\Http\Request;

class TimetableController extends Controller {
    public function printForm(){ return view('ref.print_timetable'); }
    public function print(Request $r){
        $r->validate(['reference'=>'required']);
        $rows = Timetable::where('student_reference',$r->reference)->orderBy('day_of_week')->orderBy('start_time')->get();
        return view('ref.print_timetable_result', ['reference'=>$r->reference,'rows'=>$rows]);
    }
}
