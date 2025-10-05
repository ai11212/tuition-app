<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(){
        Schema::table('payment_transactions', function (Blueprint $t) {
            if (!Schema::hasColumn('payment_transactions','paid_at')) {
                $t->dateTime('paid_at')->nullable()->after('paid_on')->index();
            }
        });
    }
    public function down(){
        Schema::table('payment_transactions', function (Blueprint $t) {
            if (Schema::hasColumn('payment_transactions','paid_at')) {
                $t->dropColumn('paid_at');
            }
        });
    }
};
