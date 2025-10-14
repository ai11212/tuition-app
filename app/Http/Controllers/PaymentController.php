<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\{Invoice, PaymentTransaction, Student};
use App\Models\Expense;

class PaymentController extends Controller
{
    /** Payments page: filters + list */
    public function take(Request $r){
        $ref  = trim($r->input('ref',''));
        $from = $r->input('from','');
        $to   = $r->input('to','');

        $hasPaidAt = Schema::hasColumn('payment_transactions','paid_at');

        $q = PaymentTransaction::query()
            ->leftJoin('invoices','payment_transactions.invoice_id','=','invoices.id')
            ->leftJoin('students','invoices.student_id','=','students.id')
            ->select([
                'payment_transactions.*',
                'invoices.id as invoice_id',
                'invoices.reference as invoice_ref',
                'students.reference as student_ref',
                DB::raw("CONCAT(COALESCE(students.first_name,''),' ',COALESCE(students.last_name,'')) as student_name")
            ]);

        if ($ref !== '')   $q->where('students.reference','LIKE',$ref.'%');
        if ($from) {
            $q->where(function($qq) use ($from, $hasPaidAt) {
                if ($hasPaidAt) $qq->whereDate('payment_transactions.paid_at','>=',$from);
                $qq->orWhereDate('payment_transactions.paid_on','>=',$from);
            });
        }
        if ($to) {
            $q->where(function($qq) use ($to, $hasPaidAt) {
                if ($hasPaidAt) $qq->whereDate('payment_transactions.paid_at','<=',$to);
                $qq->orWhereDate('payment_transactions.paid_on','<=',$to);
            });
        }

        // Order by date (prefer paid_at if exists, fallback to paid_on) then by ID
        if ($hasPaidAt) {
            $payments = $q->orderByRaw('COALESCE(payment_transactions.paid_at, payment_transactions.paid_on) DESC')
                          ->orderBy('payment_transactions.id','desc')
                          ->paginate(25)->withQueryString();
        } else {
            $payments = $q->orderBy('payment_transactions.paid_on','desc')
                          ->orderBy('payment_transactions.id','desc')
                          ->paginate(25)->withQueryString();
        }

        // If a reference filter was provided, also load matching students so
        // the 'take' form can show student-specific inputs immediately.
        $students = collect();
        $studentDetails = null;
        if ($ref !== '') {
            $students = Student::where('reference','LIKE',$ref.'%')
                ->orWhere(DB::raw("CONCAT(first_name,' ',last_name)"),'LIKE','%'.$ref.'%')
                ->limit(50)->get();
            
            // If exact reference match, load enhanced student details
            $exactStudent = Student::where('reference', $ref)->first();
            if ($exactStudent) {
                // Calculate total payments made for this student
                $totalPaid = PaymentTransaction::leftJoin('invoices', 'payment_transactions.invoice_id', '=', 'invoices.id')
                    ->where('invoices.student_id', $exactStudent->id)
                    ->sum('payment_transactions.amount');
                
                // Check if student_reference column exists in books table
                $hasStudentReferenceColumn = Schema::hasColumn('books', 'student_reference');
                

                
                // Load books for this specific student
                $assignedBooks = collect();
                $subjectBooks = collect();
                
                if ($hasStudentReferenceColumn) {
                    // Priority 1: Books directly assigned to this student reference
                    $assignedBooks = \App\Models\Book::where('student_reference', $exactStudent->reference)
                        ->orderBy('subject')->orderBy('title')->get();
                    

                } else {
                    // If no student_reference column, try to find books by matching reference field
                    $assignedBooks = \App\Models\Book::where('reference', $exactStudent->reference)
                        ->orderBy('subject')->orderBy('title')->get();

                }
                
                // Priority 2: Books for subjects that this student is studying (general books)
                $studentSubjects = \App\Models\Timetable::where('student_reference', $exactStudent->reference)
                    ->distinct('subject')
                    ->pluck('subject');
                

                    
                if ($studentSubjects->count() > 0) {
                    if ($hasStudentReferenceColumn) {
                        // Filter out books assigned to specific students (case-insensitive subject matching)
                        $subjectBooks = \App\Models\Book::where(function($q) use ($studentSubjects) {
                                foreach ($studentSubjects as $subject) {
                                    $q->orWhereRaw('LOWER(subject) = ?', [strtolower($subject)]);
                                }
                            })
                            ->where(function($q) {
                                $q->whereNull('student_reference')
                                  ->orWhere('student_reference', '');
                            })
                            ->orderBy('subject')->orderBy('title')->get();
                    } else {
                        // Fallback: show all books for subjects (backward compatibility, case-insensitive)
                        $subjectBooks = \App\Models\Book::where(function($q) use ($studentSubjects) {
                                foreach ($studentSubjects as $subject) {
                                    $q->orWhereRaw('LOWER(subject) = ?', [strtolower($subject)]);
                                }
                            })
                            ->orderBy('subject')->orderBy('title')->get();
                    }
                }
                
                // Combine both collections - prioritize assigned books
                $books = $assignedBooks->concat($subjectBooks);
                $totalBookPrice = $books->sum('price');
                
                // If no books found by either method, try alternative matching
                if ($books->count() == 0) {
                    // Try finding books where the book reference contains or matches student reference
                    $alternativeBooks = \App\Models\Book::where('reference', 'LIKE', '%' . $exactStudent->reference . '%')
                        ->orWhere('reference', $exactStudent->reference)
                        ->orderBy('subject')->orderBy('title')->get();
                        
                    if ($alternativeBooks->count() > 0) {
                        $books = $alternativeBooks;
                        $assignedBooks = $alternativeBooks;
                        $totalBookPrice = $books->sum('price');
                        

                    }
                }
                

                
                // Calculate book payments pending (assuming all books are required for the student)
                // This is the total book cost minus payments made (excluding deposit)
                $paymentsForBooks = max(0, $totalPaid - ($exactStudent->deposit ?? 0));
                $bookPaymentsPending = max(0, $totalBookPrice - $paymentsForBooks);
                
                // Calculate total payment pending
                // Payment Pending = (Student Payment + Total Book Price) - Total Paid
                $expectedTotal = ($exactStudent->payment ?? 0) + $totalBookPrice;
                $paymentPending = max(0, $expectedTotal - $totalPaid);
                
                $studentDetails = [
                    'student' => $exactStudent,
                    'total_paid' => $totalPaid,
                    'books' => $books,
                    'assigned_books' => $assignedBooks,
                    'subject_books' => $subjectBooks,
                    'student_subjects' => $studentSubjects,
                    'total_book_price' => $totalBookPrice,
                    'payments_for_books' => $paymentsForBooks,
                    'book_payments_pending' => $bookPaymentsPending,
                    'deposit' => $exactStudent->deposit ?? 0,
                    'deposit_paid' => $exactStudent->deposit_paid ?? false,
                    'payment' => $exactStudent->payment ?? 0,
                    'expected_total' => $expectedTotal,
                    'payment_pending' => $paymentPending
                ];
            }
        }

        return view('finance.payments', compact('ref','from','to','payments','students','studentDetails'));
    }

    /** Store a payment */
    public function store(Request $r){
        $data = $r->validate([
            'reference'   => 'required|string',
            'amount'      => 'required|numeric|min:0.01',
            'method'      => 'required|string', // Cash|Card|Bank
            'paid_at'     => 'nullable|date',
            'period_from' => 'nullable|date',
            'period_to'   => 'nullable|date',
            'notes'       => 'nullable|string',
        ]);
        $student = Student::where('reference',$data['reference'])->firstOrFail();

        // Use provided period dates or default to current date
        $periodFrom = $data['period_from'] ?? now()->toDateString();
        $periodTo = $data['period_to'] ?? now()->toDateString();

        $inv = Invoice::create([
            'student_id' => $student->id,
            'reference'  => 'INV-'.Str::upper(Str::random(6)),
            'period_from'=> $periodFrom,
            'period_to'  => $periodTo,
            'amount'     => $data['amount'],
            'balance'    => 0,
            'status'     => 'paid',
        ]);

        PaymentTransaction::create([
            'invoice_id' => $inv->id,
            'paid_on'    => $data['paid_at'] ? date('Y-m-d', strtotime($data['paid_at'])) : now()->toDateString(),
            // set paid_at only if the column exists (shared hosting safety)
            'paid_at'    => Schema::hasColumn('payment_transactions','paid_at') ? ($data['paid_at'] ?: now()) : null,
            'amount'     => $data['amount'],
            'method'     => $data['method'],
            'notes'      => $data['notes'] ?? null,
        ]);

        // After creating an invoice+transaction, redirect to the invoice print page
        return redirect()->route('invoice.print', $inv->id);
    }

    /** CSV export using current filters */
    public function exportCsv(Request $r){
        $ref  = trim($r->input('ref',''));
        $from = $r->input('from','');
        $to   = $r->input('to','');

        $hasPaidAt = Schema::hasColumn('payment_transactions','paid_at');

        $query = PaymentTransaction::query()
            ->leftJoin('invoices','payment_transactions.invoice_id','=','invoices.id')
            ->leftJoin('students','invoices.student_id','=','students.id')
            ->when($ref!=='' , fn($q)=>$q->where('students.reference','LIKE',$ref.'%'))
            ->when($from    , fn($q) => $q->where(function($qq) use($from,$hasPaidAt){
                if ($hasPaidAt) $qq->whereDate('payment_transactions.paid_at','>=',$from);
                $qq->orWhereDate('payment_transactions.paid_on','>=',$from);
            }))
            ->when($to      , fn($q) => $q->where(function($qq) use($to,$hasPaidAt){
                if ($hasPaidAt) $qq->whereDate('payment_transactions.paid_at','<=',$to);
                $qq->orWhereDate('payment_transactions.paid_on','<=',$to);
            }));

        // Order by date then by ID
        if ($hasPaidAt) {
            $query->orderByRaw('COALESCE(payment_transactions.paid_at, payment_transactions.paid_on) DESC');
        } else {
            $query->orderBy('payment_transactions.paid_on','desc');
        }
        
        $rows = $query->orderBy('payment_transactions.id','desc')
            ->get([
                'students.reference as Reference',
                DB::raw("CONCAT(COALESCE(students.first_name,''),' ',COALESCE(students.last_name,'')) as Student"),
                DB::raw(($hasPaidAt
                    ? 'COALESCE(payment_transactions.paid_at, CONCAT(payment_transactions.paid_on," 00:00:00"))'
                    : 'CONCAT(payment_transactions.paid_on," 00:00:00")').' as PaidAt'),
                'payment_transactions.method as Method',
                'payment_transactions.amount as Amount',
                'payment_transactions.notes as Notes',
                'invoices.reference as Invoice'
            ]);

        $filename = 'payments_'.date('Ymd_His').'.csv';
        header('Content-Type: text/csv');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        $out = fopen('php://output','w');
        fputcsv($out, ['Reference','Student','Paid at','Method','Amount','Notes','Invoice']);
        foreach ($rows as $r) {
            fputcsv($out, [$r->Reference,$r->Student,$r->PaidAt,$r->Method,number_format($r->Amount,2),$r->Notes,$r->Invoice]);
        }
        fclose($out);
        exit;
    }

    /** Print-friendly list (browser → Save as PDF) */
    public function print(Request $r){
        $ref  = trim($r->input('ref',''));
        $from = $r->input('from','');
        $to   = $r->input('to','');

        $hasPaidAt = Schema::hasColumn('payment_transactions','paid_at');

        $query = PaymentTransaction::query()
            ->leftJoin('invoices','payment_transactions.invoice_id','=','invoices.id')
            ->leftJoin('students','invoices.student_id','=','students.id')
            ->when($ref!=='' , fn($q)=>$q->where('students.reference','LIKE',$ref.'%'))
            ->when($from    , fn($q)=>$q->where(function($qq) use($from,$hasPaidAt){
                if ($hasPaidAt) $qq->whereDate('payment_transactions.paid_at','>=',$from);
                $qq->orWhereDate('payment_transactions.paid_on','>=',$from);
            }))
            ->when($to      , fn($q)=>$q->where(function($qq) use($to,$hasPaidAt){
                if ($hasPaidAt) $qq->whereDate('payment_transactions.paid_at','<=',$to);
                $qq->orWhereDate('payment_transactions.paid_on','<=',$to);
            }));

        // Order by date then by ID
        if ($hasPaidAt) {
            $query->orderByRaw('COALESCE(payment_transactions.paid_at, payment_transactions.paid_on) DESC');
        } else {
            $query->orderBy('payment_transactions.paid_on','desc');
        }
        
        $payments = $query->orderBy('payment_transactions.id','desc')
            ->get([
                'payment_transactions.*',
                'invoices.reference as invoice_ref',
                'students.reference as student_ref',
                DB::raw("CONCAT(COALESCE(students.first_name,''),' ',COALESCE(students.last_name,'')) as student_name")
            ]);

        return view('finance.payments_print', compact('payments','ref','from','to'));
    }

    /** Print a single invoice (invoice id route) */
    public function printInvoice(Invoice $invoice)
    {
        // load invoice with transactions and student
        $invoice->load(['transactions','student']);
        
        // Get student's timetable/class schedule
        $timetables = \App\Models\Timetable::where('student_id', $invoice->student_id)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
        
        return view('finance.invoice', compact('invoice', 'timetables'));
    }

    /** ACCOUNTS SUMMARY */
    public function summary(Request $r){
        $from = $r->input('from', now()->startOfMonth()->toDateString());
        $to   = $r->input('to',   now()->toDateString());

        $inTotal = PaymentTransaction::whereBetween('paid_on',[$from,$to])->sum('amount');
        $inCash  = PaymentTransaction::whereBetween('paid_on',[$from,$to])->where('method','Cash')->sum('amount');
        $inCard  = PaymentTransaction::whereBetween('paid_on',[$from,$to])->where('method','Card')->sum('amount');
        $inBank  = PaymentTransaction::whereBetween('paid_on',[$from,$to])->whereIn('method',['Bank','Transfer'])->sum('amount');

        $outTotal = class_exists(Expense::class)
            ? Expense::whereBetween('expense_on',[$from,$to])->sum('amount') : 0;

        $net = $inTotal - $outTotal;

        $inDaily  = PaymentTransaction::selectRaw('paid_on as d, SUM(amount) s')->whereBetween('paid_on',[$from,$to])->groupBy('d')->pluck('s','d');
        $outDaily = class_exists(Expense::class)
            ? Expense::selectRaw('expense_on as d, SUM(amount) s')->whereBetween('expense_on',[$from,$to])->groupBy('d')->pluck('s','d')
            : collect();

        $dates = collect($inDaily->keys())->merge($outDaily->keys())->unique()->sort()->values();
        $daily = $dates->map(fn($d)=>['date'=>$d,'in'=>(float)($inDaily[$d]??0),'out'=>(float)($outDaily[$d]??0),'net'=>(float)($inDaily[$d]??0)-(float)($outDaily[$d]??0)]);

        $weekly = collect(); $monthly = collect(); // Keep simple for now
        $invoices    = Invoice::whereBetween('period_from',[$from,$to])->count();
        $outstanding = Invoice::sum('balance');
        $breakdown   = ['cash'=>$inCash,'card'=>$inCard,'bank'=>$inBank];

        return view('finance.summary', compact(
            'from','to','inTotal','outTotal','net','breakdown','daily','weekly','monthly','invoices','outstanding'
        ));
    }
}
