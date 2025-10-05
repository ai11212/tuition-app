<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\{Student,Timetable};

class ReferenceProfileController extends Controller {
    public function form(){ return view('ref.profile_form'); }
    public function show(Request $r){
        $r->validate(['reference'=>'required']);
        $student = Student::where('reference',$r->reference)->first();
        $timetable = Timetable::where('student_reference',$r->reference)->orderBy('day_of_week')->get();
        return view('ref.profile', compact('student','timetable'))->with('reference',$r->reference);
    }
}
