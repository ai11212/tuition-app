<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Records which user issued a book, marked attendance or took a payment.
     *
     * Name only — a snapshot, so the record still shows who did it after that
     * user is deleted. No id column, no index and no foreign key: nothing else
     * needs to join on it, and student_attendances has been bitten before by a
     * foreign key blocking an index change.
     *
     * Guarded with hasColumn so this is safe to run on a database where the
     * columns were already added by hand.
     */
    private const TABLES = [
        'books'                => 'issue_date',
        'student_attendances'  => 'teacher',
        'payment_transactions' => 'notes',
    ];

    public function up(): void
    {
        foreach (self::TABLES as $table => $after) {
            if (!Schema::hasTable($table) || Schema::hasColumn($table, 'created_by_name')) {
                continue;
            }

            Schema::table($table, function (Blueprint $t) use ($after) {
                $t->string('created_by_name', 100)->nullable()->after($after);
            });
        }
    }

    public function down(): void
    {
        foreach (array_keys(self::TABLES) as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'created_by_name')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn('created_by_name');
                });
            }
        }
    }
};
