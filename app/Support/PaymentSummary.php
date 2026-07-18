<?php
namespace App\Support;

use App\Models\Book;
use App\Models\PaymentTransaction;
use App\Models\Student;
use App\Models\Timetable;
use Illuminate\Support\Facades\Schema;

/**
 * Single source of truth for a family's payment summary.
 *
 * Mirrors the Take Payments page (PaymentController::take) exactly — every
 * page that shows Total Paid / Expected / Payment Pending / Book Pending /
 * Credit must read from here so the figures can never diverge.
 */
class PaymentSummary
{
    /** Total of all payments recorded against the student's invoices */
    public static function totalPaid(Student $student): float
    {
        return (float) PaymentTransaction::leftJoin('invoices', 'payment_transactions.invoice_id', '=', 'invoices.id')
            ->where('invoices.student_id', $student->id)
            ->sum('payment_transactions.amount');
    }

    /** Family book total — assigned books + unassigned subject-matched books (take()'s logic) */
    public static function totalBookPrice(Student $student): float
    {
        $hasStudentReferenceColumn = Schema::hasColumn('books', 'student_reference');

        if ($hasStudentReferenceColumn) {
            $assignedBooks = Book::leftJoin('students', 'books.student_reference', '=', 'students.id')
                ->where('students.reference', $student->reference)
                ->select('books.*')
                ->get();
        } else {
            $assignedBooks = Book::where('reference', $student->reference)->get();
        }

        $studentSubjects = Timetable::where('student_reference', $student->reference)
            ->distinct('subject')
            ->pluck('subject');

        $subjectBooks = collect();
        if ($studentSubjects->count() > 0) {
            $q = Book::where(function ($qq) use ($studentSubjects) {
                foreach ($studentSubjects as $subject) {
                    $qq->orWhereRaw('LOWER(subject) = ?', [strtolower((string) $subject)]);
                }
            });
            if ($hasStudentReferenceColumn) {
                $q->where(function ($qq) {
                    $qq->whereNull('student_reference')->orWhere('student_reference', '');
                });
            }
            $subjectBooks = $q->get();
        }

        $total = $assignedBooks->sum('price') + $subjectBooks->sum('price');

        // take()'s last-resort fallback when nothing matched
        if ($assignedBooks->count() + $subjectBooks->count() === 0) {
            $total = Book::where('reference', 'LIKE', '%' . $student->reference . '%')
                ->orWhere('reference', $student->reference)
                ->sum('price');
        }

        return (float) $total;
    }

    /** Expected total: (pending_amount ?? payment) + family book prices */
    public static function expectedTotal(Student $student): float
    {
        return (float) (($student->pending_amount ?? $student->payment ?? 0) + self::totalBookPrice($student));
    }

    /** Full summary array (keys match the Take Payments page's studentDetails) */
    public static function forStudent(Student $student): array
    {
        $totalPaid = self::totalPaid($student);
        $totalBookPrice = self::totalBookPrice($student);
        $expectedTotal = (float) (($student->pending_amount ?? $student->payment ?? 0) + $totalBookPrice);

        $paymentsForBooks = max(0, $totalPaid - ($student->deposit ?? 0));

        return [
            'total_paid'            => $totalPaid,
            'total_book_price'      => $totalBookPrice,
            'expected_total'        => $expectedTotal,
            'payment_pending'       => max(0, $expectedTotal - $totalPaid),
            'book_payments_pending' => max(0, $totalBookPrice - $paymentsForBooks),
            'credit_balance'        => max(0, $totalPaid - $expectedTotal),
        ];
    }
}
