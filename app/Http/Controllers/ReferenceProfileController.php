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
        
        // Calculate payment details (same logic as PaymentController)
        $totalPaid = PaymentTransaction::leftJoin('invoices', 'payment_transactions.invoice_id', '=', 'invoices.id')
            ->where('invoices.student_id', $student->id)
            ->sum('payment_transactions.amount');
        
        // Get assigned books - Note: student_reference stores student ID, not reference string
        $assignedBooks = Book::leftJoin('students', 'books.student_reference', '=', 'students.id')
            ->where('students.reference', $student->reference)
            ->select('books.*')
            ->get();
        
        $studentSubjects = Timetable::where('student_id', $student->id)->pluck('subject')->unique();
        $subjectBooks = Book::whereNull('student_reference')->whereIn('subject', $studentSubjects)->get();
        $totalBookPrice = $assignedBooks->sum('price') + $subjectBooks->sum('price');
        
        $paymentsForBooks = max(0, $totalPaid - ($student->deposit ?? 0));
        $bookPaymentsPending = max(0, $totalBookPrice - $paymentsForBooks);
        $expectedTotal = ($student->payment ?? 0) + $totalBookPrice;
        $paymentPending = max(0, $expectedTotal - $totalPaid);
        
        $paymentDetails = [
            'total_paid' => $totalPaid,
            'expected_total' => $expectedTotal,
            'payment_pending' => $paymentPending,
            'book_payments_pending' => $bookPaymentsPending,
            'total_book_price' => $totalBookPrice
        ];
        
        return view('ref.profile', compact('student', 'allSiblings', 'siblingsWithTimetables', 'paymentDetails'));
    }
}
