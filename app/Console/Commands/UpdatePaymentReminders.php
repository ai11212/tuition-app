<?php

namespace App\Console\Commands;

use App\Models\Student;
use App\Models\PaymentReminder;
use App\Models\PaymentTransaction;
use App\Models\ReminderSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdatePaymentReminders extends Command
{
    protected $signature = 'reminders:update';
    protected $description = 'Update payment reminders for all active students';

    public function handle()
    {
        if (!config('reminders.enabled')) {
            $this->info('Payment Reminders module is disabled');
            return 0;
        }

        $this->info('Updating payment reminders...');

        // Clear old reminders (except skipped ones)
        PaymentReminder::where('is_holiday', false)->delete();

        // Get settings
        $weeklyDue = ReminderSetting::get('weekly_due_days', 7);
        $weeklyOverdue = ReminderSetting::get('weekly_overdue_days', 10);
        $monthlyDue = ReminderSetting::get('monthly_due_days', 30);
        $monthlyOverdue = ReminderSetting::get('monthly_overdue_days', 33);

        // Get all active students grouped by reference (one per family)
        // Use ->first() to match Payment Page logic (PaymentController line 68)
        $references = Student::where('status', 'active')
            ->select('reference')
            ->distinct()
            ->pluck('reference');

        $created = 0;

        foreach ($references as $reference) {
            // Get FIRST student for this reference (matching Payment Page logic exactly)
            $student = Student::where('reference', $reference)->first();
            
            if (!$student) continue;

            // Get all siblings with same reference
            $siblings = Student::where('reference', $reference)->get();

            // Get last payment (checking ALL siblings' payments)
            // This ensures we find the most recent payment across all siblings
            $siblingIds = $siblings->pluck('id');
            $lastPayment = PaymentTransaction::leftJoin('invoices', 'payment_transactions.invoice_id', '=', 'invoices.id')
                ->whereIn('invoices.student_id', $siblingIds)
                ->orderBy('payment_transactions.paid_on', 'desc')
                ->first();

            // Calculate days since payment
            $daysSince = $lastPayment 
                ? abs(now()->diffInDays($lastPayment->paid_on))
                : 999; // Never paid

            // Determine period (default to weekly)
            $period = $student->period ?? 'weekly';

            // Get thresholds based on period
            if ($period === 'monthly') {
                $dueThreshold = $monthlyDue;
                $overdueThreshold = $monthlyOverdue;
            } else {
                $dueThreshold = $weeklyDue;
                $overdueThreshold = $weeklyOverdue;
            }

            // Determine status
            if ($daysSince >= $overdueThreshold) {
                $status = 'overdue';
            } elseif ($daysSince >= $dueThreshold) {
                $status = 'due';
            } else {
                continue; // Not due yet, skip
            }

            // Calculate expected amount matching Payment Page logic:
            // Expected = (pending_amount OR payment) + total_book_price - total_paid
            
            // Get books for this reference (all siblings' books)
            $totalBookPrice = DB::table('books')
                ->leftJoin('students', 'books.student_reference', '=', 'students.id')
                ->where('students.reference', $student->reference)
                ->sum('books.price');
            
            // Get base amount (pending_amount with fallback to payment)
            $baseAmount = $student->pending_amount ?? $student->payment ?? 0;
            
            // Calculate total paid (for ALL siblings under this reference)
            $totalPaid = PaymentTransaction::leftJoin('invoices', 'payment_transactions.invoice_id', '=', 'invoices.id')
                ->whereIn('invoices.student_id', $siblingIds)
                ->sum('payment_transactions.amount');
            
            // Total expected = base + books
            $expectedTotal = $baseAmount + $totalBookPrice;
            
            // Payment Pending = expected - paid (matching Payment Page exactly)
            $expectedAmount = max(0, $expectedTotal - $totalPaid);

            // Skip if no amount pending
            if ($expectedAmount <= 0) {
                continue;
            }

            // Collect sibling names
            $siblingNames = $siblings->map(function($s) {
                return trim($s->first_name . ' ' . $s->last_name);
            })->toArray();

            // Calculate next due date
            $nextDueDate = $lastPayment
                ? \Carbon\Carbon::parse($lastPayment->paid_on)->addDays($dueThreshold)
                : now();

            // Create reminder (only one per reference, not per sibling)
            $existing = PaymentReminder::where('reference', $student->reference)->first();
            if (!$existing) {
                PaymentReminder::create([
                    'student_id' => $student->id,
                    'reference' => $student->reference,
                    'student_names' => $siblingNames,
                    'expected_amount' => $expectedAmount,
                    'last_payment_date' => $lastPayment?->paid_on,
                    'last_payment_amount' => $lastPayment?->amount,
                    'days_since_payment' => $daysSince,
                    'next_due_date' => $nextDueDate,
                    'period' => $period,
                    'status' => $status,
                    'calculated_at' => now(),
                ]);

                $created++;
            }
        }

        $this->info("Payment reminders updated! Created: {$created}");
        return 0;
    }
}
