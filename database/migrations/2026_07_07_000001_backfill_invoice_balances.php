<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Backfill invoice balance snapshots. True historical expected totals are
     * not reconstructible (fees/books changed over time), so approximate: per
     * student, walk invoices chronologically and set
     *   balance_i = max(0, current expected − cumulative payments through i).
     * The newest invoice then matches today's outstanding; older ones form a
     * consistent decreasing series.
     */
    public function up(): void
    {
        $students = DB::table('students')->get(['id', 'reference', 'pending_amount', 'payment']);

        foreach ($students as $student) {
            $invoices = DB::table('invoices')->where('student_id', $student->id)->orderBy('id')->get(['id']);
            if ($invoices->isEmpty()) {
                continue;
            }

            $assignedBooksPrice = DB::table('books')
                ->leftJoin('students', 'books.student_reference', '=', 'students.id')
                ->where('students.reference', $student->reference)
                ->sum('books.price');

            $subjects = DB::table('timetables')->where('student_id', $student->id)->pluck('subject')->unique()->filter();
            $subjectBooksPrice = $subjects->isEmpty() ? 0 : DB::table('books')
                ->whereNull('student_reference')
                ->whereIn('subject', $subjects)
                ->sum('price');

            $expected = ($student->pending_amount ?? $student->payment ?? 0) + $assignedBooksPrice + $subjectBooksPrice;

            $cumulativePaid = 0;
            foreach ($invoices as $inv) {
                $cumulativePaid += DB::table('payment_transactions')->where('invoice_id', $inv->id)->sum('amount');
                DB::table('invoices')->where('id', $inv->id)->update(['balance' => max(0, $expected - $cumulativePaid)]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('invoices')->update(['balance' => 0]);
    }
};
