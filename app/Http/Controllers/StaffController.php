<?php
namespace App\Http\Controllers;
use App\Models\Staff;
use Illuminate\Http\Request;

class StaffController extends Controller {
    public function index(){ $items = Staff::latest()->paginate(20); return view('staff.index', compact('items')); }
    public function create(){ return view('staff.create'); }
    public function store(Request $r){
        $data=$r->validate(['name'=>'required','phone'=>'nullable','email'=>'nullable|email','role'=>'nullable','status'=>'nullable']);
        Staff::create($data); return redirect()->route('staff.index')->with('ok','Staff added');
    }
    public function edit(Staff $staff){ return view('staff.edit', compact('staff')); }
    public function update(Request $r, Staff $staff){
        $data=$r->validate(['name'=>'required','phone'=>'nullable','email'=>'nullable|email','role'=>'nullable','status'=>'nullable']);
        $staff->update($data); return back()->with('ok','Updated');
    }
    public function destroy(Staff $staff){ $staff->delete(); return back()->with('ok','Deleted'); }
}
