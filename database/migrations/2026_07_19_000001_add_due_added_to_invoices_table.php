<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations. Records the Payment Due amount that was submitted
     * together with this invoice's payment on the Record a Payment form, so
     * deleting the payment can reverse exactly that due from the student's
     * fee base. NULL = payment recorded without a paired due (or pre-dates
     * this column) — deletion leaves the fee base untouched, as before.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('invoices', 'due_added')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->decimal('due_added', 10, 2)->nullable()->after('balance');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('invoices', 'due_added')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropColumn('due_added');
            });
        }
    }
};
