<?php
namespace App\Http\Controllers;
use App\Models\{Student,Staff,Book,Invoice};
class DashboardController extends Controller{
    public function index(){
        $students = class_exists(Student::class) ? Student::count() : 0;
        $staff    = class_exists(Staff::class) ? Staff::count() : 0;
        $books    = class_exists(Book::class) ? Book::count() : 0;
        $pending  = class_exists(Invoice::class) ? (Invoice::where('status','pending')->count()) : 0;
        return view('dashboard', compact('students','staff','books','pending'));
    }
}
