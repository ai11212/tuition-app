<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\{Student,Timetable,PaymentTransaction,Book};

class ReferenceProfileController extends Controller {
    public function form(){ return view('ref.profile_form'); }
    
    public function show(Request $r){
        // Build search query
        $query = Student::query();
        
        if ($r->filled('reference')) {
            // If coming from search results (exact match), use exact comparison
            // If user is searching, use LIKE for partial match
            if ($r->has('exact')) {
                $query->where('reference', $r->reference);
            } else {
                $query->where('reference', 'LIKE', '%' . $r->reference . '%');
            }
        }
        if ($r->filled('first_name')) {
            $query->where('first_name', 'LIKE', '%' . $r->first_name . '%');
        }
        if ($r->filled('last_name')) {
            $query->where('last_name', 'LIKE', '%' . $r->last_name . '%');
        }
        if ($r->filled('dob')) {
            $query->where('dob', $r->dob);
        }
        if ($r->filled('year')) {
            $query->where('year', $r->year);
        }

        $students = $query->get();
        
        // No results
        if ($students->count() == 0) {
            return redirect()->route('ref.form')->with('warning', 'No students found matching your search criteria');
        }
        
        // Multiple results AND not exact match - show search results list
        if ($students->count() > 1 && !$r->has('exact')) {
            return view('ref.search_results', compact('students'));
        }
        
        // Single result OR exact match (show all siblings together) - show full profile
        // For siblings, show the first student as primary but include all sibling data
        $student = $students->first();
        $allSiblings = $students; // All students with same reference (for family view)
        
        // Get timetable for EACH sibling separately (not mixed)
        $siblingsWithTimetables = $allSiblings->map(function($sibling) {
            return [
                'student' => $sibling,
                'timetable' => Timetable::where('student_id', $sibling->id)
                    ->orderBy('day_of_week')
                    ->orderBy('start_time')
                    ->get()
            ];
        });
        
        // Payment summary — SHARED source of truth (App\Support\PaymentSummary),
        // guaranteed identical to the Finance → Payments page. The old inline
        // copy here omitted pending_amount, which made Payment Pending wrong.
        $paymentDetails = \App\Support\PaymentSummary::forStudent($student);
        
        return view('ref.profile', compact('student', 'allSiblings', 'siblingsWithTimetables', 'paymentDetails'));
    }
}
