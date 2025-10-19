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
        
        // Multiple results - show list
        if ($students->count() > 1) {
            return view('ref.search_results', compact('students'));
        }
        
        // Single result - show full details
        $student = $students->first();
        $timetable = Timetable::where('student_reference', $student->reference)
            ->orderBy('day_of_week')
            ->get();
        
        // Calculate payment details (same logic as PaymentController)
        $totalPaid = PaymentTransaction::leftJoin('invoices', 'payment_transactions.invoice_id', '=', 'invoices.id')
            ->where('invoices.student_id', $student->id)
            ->sum('payment_transactions.amount');
        
        $assignedBooks = Book::where('student_reference', $student->reference)->get();
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
        
        return view('ref.profile', compact('student', 'timetable', 'paymentDetails'));
    }
}
