<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\PaymentTransaction;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentVerificationController extends Controller
{
    /**
     * Show payment status for all students
     * Compare expected payments vs actual payments received
     */
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $status = $request->input('status', 'all'); // all, paid, pending, overpaid
        
        // Get all active students with payment calculations
        $students = Student::query()
            ->when($search, function($q) use ($search) {
                $q->where('reference', 'LIKE', $search . '%')
                  ->orWhere(DB::raw("CONCAT(first_name,' ',last_name)"), 'LIKE', '%' . $search . '%');
            })
            ->where('status', 'active')
            ->get()
            ->map(function($student) {
                // Calculate total payments made by this student
                $totalPaid = PaymentTransaction::leftJoin('invoices', 'payment_transactions.invoice_id', '=', 'invoices.id')
                    ->where('invoices.student_id', $student->id)
                    ->sum('payment_transactions.amount');
                
                // Expected amount (deposit + payment)
                $expectedTotal = ($student->deposit ?? 0) + ($student->payment ?? 0);
                
                // Balance
                $balance = $expectedTotal - $totalPaid;
                
                // Payment status
                if ($balance < -10) {
                    $paymentStatus = 'overpaid';
                } elseif (abs($balance) <= 10) {
                    $paymentStatus = 'paid';
                } else {
                    $paymentStatus = 'pending';
                }
                
                // Get last payment date
                $lastPayment = PaymentTransaction::leftJoin('invoices', 'payment_transactions.invoice_id', '=', 'invoices.id')
                    ->where('invoices.student_id', $student->id)
                    ->orderBy('payment_transactions.paid_on', 'desc')
                    ->first();
                
                return (object)[
                    'id' => $student->id,
                    'reference' => $student->reference,
                    'name' => trim($student->first_name . ' ' . $student->last_name),
                    'deposit' => $student->deposit ?? 0,
                    'payment' => $student->payment ?? 0,
                    'expected_total' => $expectedTotal,
                    'total_paid' => $totalPaid,
                    'balance' => $balance,
                    'status' => $paymentStatus,
                    'last_payment_date' => $lastPayment ? $lastPayment->paid_on : null,
                    'guardian_phone' => $student->guardian_phone,
                ];
            })
            ->when($status != 'all', function($collection) use ($status) {
                return $collection->where('status', $status);
            })
            ->sortBy('name');
        
        // Summary statistics
        $summary = [
            'total_students' => $students->count(),
            'paid_up' => $students->where('status', 'paid')->count(),
            'pending' => $students->where('status', 'pending')->count(),
            'overpaid' => $students->where('status', 'overpaid')->count(),
            'total_expected' => $students->sum('expected_total'),
            'total_received' => $students->sum('total_paid'),
            'total_pending' => $students->where('status', 'pending')->sum('balance'),
        ];
        
        return view('finance.payment-verification', compact('students', 'search', 'status', 'summary'));
    }
}
