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
                'invoices.period_from',
                'invoices.period_to',
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
                
                // Get latest payment date for this student (for smart book detection)
                // Use paid_at if available, fallback to paid_on for older records
                $latestPaymentResult = PaymentTransaction::leftJoin('invoices', 'payment_transactions.invoice_id', '=', 'invoices.id')
                    ->where('invoices.student_id', $exactStudent->id)
                    ->orderBy('payment_transactions.paid_at', 'desc')
                    ->orderBy('payment_transactions.paid_on', 'desc')
                    ->select(DB::raw('COALESCE(payment_transactions.paid_at, payment_transactions.paid_on) as payment_date'))
                    ->first();
                
                $latestPaymentDate = $latestPaymentResult ? $latestPaymentResult->payment_date : null;
                
                // Check if student_reference column exists in books table
                $hasStudentReferenceColumn = Schema::hasColumn('books', 'student_reference');
                

                
                // Load books for this specific student
                $assignedBooks = collect();
                $subjectBooks = collect();
                
                if ($hasStudentReferenceColumn) {
                    // Priority 1: Books directly assigned to ALL students under this reference (all siblings)
                    // Note: student_reference now stores student ID, so we join on ID
                    $assignedBooks = \App\Models\Book::leftJoin('students', 'books.student_reference', '=', 'students.id')
                        ->where('students.reference', $exactStudent->reference)
                        ->select('books.*', 'students.first_name', 'students.last_name', 'students.id as student_id')
                        ->orderBy('students.first_name')
                        ->orderBy('books.subject')
                        ->orderBy('books.title')
                        ->get();
                    

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
                
                // Get latest book creation date for smart detection
                $latestBookDate = $assignedBooks->count() > 0 ? ($assignedBooks->max('issue_date') ?? $assignedBooks->max('created_at')) : null;
                
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
                // Payment Pending = (Pending Amount OR Student Payment + Total Book Price) - Total Paid
                $expectedTotal = ($exactStudent->pending_amount ?? $exactStudent->payment ?? 0) + $totalBookPrice;
                $paymentPending = max(0, $expectedTotal - $totalPaid);
                
                // Smart Detection: Filter books to show only UNPAID books
                // Logic: Show only books that were added AFTER the last payment
                // This way, previously paid books stay hidden, and only new books appear
                
                $unpaidBooks = collect();
                $showBooks = false;
                
                if ($totalPaid == 0 || !$latestPaymentDate) {
                    // No payments made yet - show all books
                    $unpaidBooks = $assignedBooks;
                    $showBooks = $assignedBooks->count() > 0;
                } else {
                    // Filter books: show only those issued AFTER the last payment
                    $unpaidBooks = $assignedBooks->filter(function($book) use ($latestPaymentDate) {
                        $issued = $book->issue_date ?? $book->created_at;
                        if (!$issued) return false;
                        $issuedDay = $issued->format('Y-m-d');
                        $paymentDay = date('Y-m-d', strtotime($latestPaymentDate));
                        if ($issuedDay !== $paymentDay) return $issuedDay > $paymentDay;
                        // Issued same day as the last payment: fall back to precise entry time
                        return $book->created_at && strtotime($book->created_at) > strtotime($latestPaymentDate);
                    });
                    $showBooks = $unpaidBooks->count() > 0;
                }
                
                $studentDetails = [
                    'student' => $exactStudent,
                    'total_paid' => $totalPaid,
                    'books' => $books,
                    'assigned_books' => $unpaidBooks,  // Show only unpaid books
                    'all_assigned_books' => $assignedBooks,  // Keep all books for reference
                    'subject_books' => $subjectBooks,
                    'student_subjects' => $studentSubjects,
                    'total_book_price' => $totalBookPrice,
                    'payments_for_books' => $paymentsForBooks,
                    'book_payments_pending' => $bookPaymentsPending,
                    'deposit' => $exactStudent->deposit ?? 0,
                    'deposit_paid' => $exactStudent->deposit_paid ?? false,
                    'payment' => $exactStudent->payment ?? 0,
                    'payment_plan' => $exactStudent->payment_plan ?? null,
                    'pending_amount' => $exactStudent->pending_amount ?? null,
                    'expected_total' => $expectedTotal,
                    'payment_pending' => $paymentPending,
                    'show_books' => $showBooks,
                    'latest_payment_date' => $latestPaymentDate,
                    'latest_book_date' => $latestBookDate
                ];

                // Credit balance: overpayment surplus, computed — the formula already
                // offsets it against future dues automatically
                $studentDetails['credit_balance'] = max(0, $totalPaid - $expectedTotal);

                // --- Attendance summary for the Student Info popup (DISPLAY ONLY;
                // --- does NOT affect Payment Pending or any existing calculation) ---
                $familyStudents = Student::where('reference', $exactStudent->reference)->orderBy('id')->get();
                $attendanceSummary = [];
                $attendanceTotalAmount = 0;
                foreach ($familyStudents as $fs) {
                    $presentCount = \App\Models\StudentAttendance::where('student_id', $fs->id)
                        ->where('status', 'present')
                        ->when($from, fn($q) => $q->whereDate('date', '>=', $from))
                        ->when($to,   fn($q) => $q->whereDate('date', '<=', $to))
                        ->count();
                    $hours  = $presentCount * 2;
                    $rate   = $fs->hourly_rate; // nullable
                    $amount = $rate !== null ? $hours * (float) $rate : null;
                    if ($amount !== null) $attendanceTotalAmount += $amount;

                    $records = \App\Models\StudentAttendance::where('student_id', $fs->id)
                        ->when($from, fn($q) => $q->whereDate('date', '>=', $from))
                        ->when($to,   fn($q) => $q->whereDate('date', '<=', $to))
                        ->orderBy('date', 'desc')->limit(300)
                        ->get(['date','time','subject','teacher','status'])
                        ->map(fn($a) => [
                            'date'    => \Carbon\Carbon::parse($a->date)->format('d/m/Y'),
                            'time'    => $a->time ?: '-',
                            'subject' => $a->subject ?: '-',
                            'teacher' => $a->teacher ?: '-',
                            'status'  => $a->status,
                        ])->values();

                    $attendanceSummary[$fs->id] = [
                        'name'    => trim($fs->first_name.' '.$fs->last_name),
                        'present' => $presentCount,
                        'hours'   => $hours,
                        'rate'    => $rate,
                        'amount'  => $amount,
                        'records' => $records,
                    ];
                }
                $studentDetails['attendance_summary'] = $attendanceSummary;
                $studentDetails['attendance_total_amount'] = $attendanceTotalAmount;
            }
        }

        return view('finance.payments', compact('ref','from','to','payments','students','studentDetails'));
    }

    /** Store a payment and/or add a payment due (two-field workflow) */
    public function store(Request $r){
        // Blank/zero fields count as "not provided"
        if ((float) $r->input('payment_due', 0) <= 0) $r->merge(['payment_due' => null]);
        if ((float) $r->input('amount', 0) <= 0) $r->merge(['amount' => null]);

        $data = $r->validate([
            'reference'   => 'required|string',
            'payment_due' => 'nullable|numeric|min:0.01',
            'amount'      => 'nullable|numeric|min:0.01|required_without:payment_due',
            'method'      => 'nullable|required_with:amount|string', // Cash|Card|Bank
            'purpose'     => 'nullable|string|in:tuition,books,deposit,other',
            'paid_at'     => 'nullable|date',
            'period_from' => 'nullable|date',
            'period_to'   => 'nullable|date',
            'notes'       => 'nullable|string',
        ]);
        $student = Student::where('reference',$data['reference'])->firstOrFail();

        // Payment Due: raises the family's fee base (same semantics as the old
        // Add button). Any credit from earlier overpayment offsets it automatically,
        // because Payment Pending = max(0, expected − paid).
        if (!empty($data['payment_due'])) {
            $student->update([
                'pending_amount' => ($student->pending_amount ?? $student->payment ?? 0) + $data['payment_due'],
            ]);
            $student->refresh();
        }

        // Due-only submission: nothing was paid, so no invoice/transaction
        if (empty($data['amount'])) {
            $expected = $this->expectedForStudent($student);
            $paid = $this->paidForStudent($student);
            $outstanding = max(0, $expected - $paid);
            $credit = max(0, $paid - $expected);

            return redirect()->route('payments', ['ref' => $data['reference']])
                ->with('ok', 'Payment due of £' . number_format($data['payment_due'], 2)
                    . ' added. Outstanding now: £' . number_format($outstanding, 2)
                    . ($credit > 0 ? ' · Credit balance: £' . number_format($credit, 2) : ''));
        }

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

        $hasPaidAtColumn = Schema::hasColumn('payment_transactions','paid_at');
        
        // Convert date input to datetime for paid_at column
        $paidAtValue = null;
        if ($hasPaidAtColumn) {
            if (!empty($data['paid_at'])) {
                // Use the selected date but with current time (not midnight)
                $selectedDate = date('Y-m-d', strtotime($data['paid_at']));
                $currentTime = date('H:i:s');
                $paidAtValue = $selectedDate . ' ' . $currentTime;
            } else {
                // Use current datetime if no date provided
                $paidAtValue = now();
            }
        }
        
        $transactionData = [
            'invoice_id' => $inv->id,
            'paid_on'    => !empty($data['paid_at']) ? date('Y-m-d', strtotime($data['paid_at'])) : now()->toDateString(),
            'paid_at'    => $paidAtValue,
            'amount'     => $data['amount'],
            'method'     => $data['method'],
            'notes'      => $data['notes'] ?? null,
        ];
        
        // Add purpose if column exists (backward compatibility)
        if (Schema::hasColumn('payment_transactions','purpose')) {
            $transactionData['purpose'] = $data['purpose'] ?? 'tuition';
        }
        
        PaymentTransaction::create($transactionData);

        // Snapshot the remaining balance onto this invoice so reprints always
        // show the balance as of this payment (invoice.blade.php reads it)
        $inv->update(['balance' => max(0, $this->expectedForStudent($student) - $this->paidForStudent($student))]);

        // After creating an invoice+transaction, redirect to the invoice print page
        return redirect()->route('invoice.print', $inv->id);
    }

    /** Expected total for a student — delegates to the shared source of truth */
    private function expectedForStudent(Student $student): float
    {
        return \App\Support\PaymentSummary::expectedTotal($student);
    }

    /** Total paid across the student's invoices — shared source of truth */
    private function paidForStudent(Student $student): float
    {
        return \App\Support\PaymentSummary::totalPaid($student);
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

        // Payment breakdown by purpose (if column exists)
        $paymentBreakdown = [];
        if (Schema::hasColumn('payment_transactions','purpose')) {
            $paymentBreakdown = [
                'tuition' => PaymentTransaction::whereBetween('paid_on',[$from,$to])->where('purpose','tuition')->sum('amount'),
                'books'   => PaymentTransaction::whereBetween('paid_on',[$from,$to])->where('purpose','books')->sum('amount'),
                'deposit' => PaymentTransaction::whereBetween('paid_on',[$from,$to])->where('purpose','deposit')->sum('amount'),
                'other'   => PaymentTransaction::whereBetween('paid_on',[$from,$to])->where('purpose','other')->sum('amount'),
            ];
        }

        $outTotal = class_exists(Expense::class)
            ? Expense::whereBetween('expense_on',[$from,$to])->sum('amount') : 0;

        // Money Out breakdown by payment method (display only — outTotal unchanged)
        $outBreakdown = ['cash' => 0, 'card' => 0, 'bank' => 0];
        if (class_exists(Expense::class)) {
            $outBreakdown = [
                'cash' => Expense::whereBetween('expense_on',[$from,$to])->where('method','Cash')->sum('amount'),
                'card' => Expense::whereBetween('expense_on',[$from,$to])->where('method','Card')->sum('amount'),
                'bank' => Expense::whereBetween('expense_on',[$from,$to])->whereIn('method',['Bank','Transfer'])->sum('amount'),
            ];
        }

        // Net breakdown per method (display only — derived from the two lines above)
        $netBreakdown = [
            'cash' => $inCash - $outBreakdown['cash'],
            'card' => $inCard - $outBreakdown['card'],
            'bank' => $inBank - $outBreakdown['bank'],
        ];

        // Expense breakdown by category
        $expensesByCategory = [];
        if (class_exists(Expense::class)) {
            $expensesByCategory = Expense::selectRaw('category, SUM(amount) as total')
                ->whereBetween('expense_on', [$from, $to])
                ->groupBy('category')
                ->pluck('total', 'category')
                ->toArray();
        }

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
            'from','to','inTotal','outTotal','net','breakdown','outBreakdown','netBreakdown','paymentBreakdown','expensesByCategory','daily','weekly','monthly','invoices','outstanding'
        ));
    }

    /**
     * Update an existing payment
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'amount'      => 'required|numeric|min:0.01',
            'method'      => 'required|string|in:Cash,Card,Bank,Transfer',
            'purpose'     => 'nullable|string|in:tuition,books,deposit,other',
            'paid_at'     => 'required|date',
            'period_from' => 'nullable|date',
            'period_to'   => 'nullable|date',
            'notes'       => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $payment = PaymentTransaction::with('invoice')->findOrFail($id);
            
            // Update payment transaction
            $paymentData = [
                'amount'  => $data['amount'],
                'method'  => $data['method'],
                'paid_on' => date('Y-m-d', strtotime($data['paid_at'])),
                'notes'   => $data['notes'] ?? null,
            ];
            
            // Add optional columns if they exist (backward compatibility)
            if (Schema::hasColumn('payment_transactions','paid_at')) {
                $paymentData['paid_at'] = $data['paid_at'];
            }
            if (Schema::hasColumn('payment_transactions','purpose')) {
                $paymentData['purpose'] = $data['purpose'] ?? 'tuition';
            }
            
            $payment->update($paymentData);
            
            // Re-snapshot this invoice's balance: expected minus payments up to
            // and including this (now-edited) payment, in chronological order
            $balance = 0;
            $invStudent = Student::find($payment->invoice->student_id);
            if ($invStudent) {
                $paidThrough = PaymentTransaction::leftJoin('invoices', 'payment_transactions.invoice_id', '=', 'invoices.id')
                    ->where('invoices.student_id', $invStudent->id)
                    ->where(function ($q) use ($payment) {
                        $q->where('payment_transactions.paid_on', '<', $payment->paid_on)
                          ->orWhere(function ($qq) use ($payment) {
                              $qq->where('payment_transactions.paid_on', $payment->paid_on)
                                 ->where('payment_transactions.id', '<=', $payment->id);
                          });
                    })
                    ->sum('payment_transactions.amount');
                $balance = max(0, $this->expectedForStudent($invStudent) - $paidThrough);
            }

            // Update related invoice
            $payment->invoice->update([
                'amount'      => $data['amount'],
                'period_from' => $data['period_from'] ?? $payment->invoice->period_from,
                'period_to'   => $data['period_to'] ?? $payment->invoice->period_to,
                'balance'     => $balance,
            ]);
            
            DB::commit();
            return redirect()->route('payments', request()->only('ref','from','to'))
                             ->with('success', 'Payment updated successfully!');
                             
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('payments', request()->only('ref','from','to'))
                             ->with('error', 'Failed to update payment: ' . $e->getMessage());
        }
    }

    /**
     * Delete a payment (cascade deletes invoice)
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $payment = PaymentTransaction::with('invoice')->findOrFail($id);
            $invoiceRef = $payment->invoice->reference ?? 'N/A';
            
            // Delete invoice first (if exists), then payment
            if ($payment->invoice) {
                $payment->invoice->delete();
            }
            $payment->delete();
            
            DB::commit();
            return redirect()->route('payments', request()->only('ref','from','to'))
                             ->with('success', "Payment and Invoice {$invoiceRef} deleted successfully!");
                             
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('payments', request()->only('ref','from','to'))
                             ->with('error', 'Failed to delete payment: ' . $e->getMessage());
        }
    }

    /**
     * Update pending amount for a student
     */
    public function updatePendingAmount(Request $request, Student $student)
    {
        $request->validate([
            'pending_amount' => 'required|numeric|min:0'
        ]);

        $student->update([
            'pending_amount' => $request->pending_amount
        ]);

        $outstanding = max(0, $this->expectedForStudent($student) - $this->paidForStudent($student));

        return redirect()->back()->with('success',
            "Fee updated to £" . number_format($student->pending_amount, 2)
            . " · Outstanding now: £" . number_format($outstanding, 2));
    }

    /**
     * Add amount to existing pending amount (incremental)
     */
    public function addPendingAmount(Request $request, Student $student)
    {
        $request->validate([
            'amount_to_add' => 'required|numeric|min:0'
        ]);

        $currentPending = $student->pending_amount ?? $student->payment ?? 0;
        $amountToAdd = $request->amount_to_add;
        $newPending = $currentPending + $amountToAdd;

        $student->update([
            'pending_amount' => $newPending
        ]);

        $outstanding = max(0, $this->expectedForStudent($student) - $this->paidForStudent($student));

        return redirect()->back()->with('success',
            "Added £" . number_format($amountToAdd, 2) . " to the fee. Total fee: £" . number_format($newPending, 2)
            . " · Outstanding now: £" . number_format($outstanding, 2));
    }

    /**
     * Set pending amount to specific value (replaces existing)
     */
    public function setPendingAmount(Request $request, Student $student)
    {
        $request->validate([
            'amount_to_set' => 'required|numeric|min:0'
        ]);

        $amountToSet = $request->amount_to_set;

        $student->update([
            'pending_amount' => $amountToSet
        ]);

        $outstanding = max(0, $this->expectedForStudent($student) - $this->paidForStudent($student));

        return redirect()->back()->with('success',
            "Fee set to £" . number_format($amountToSet, 2)
            . " · Outstanding now: £" . number_format($outstanding, 2));
    }
}
