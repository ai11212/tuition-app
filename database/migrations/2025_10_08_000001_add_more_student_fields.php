<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (!Schema::hasTable('students')) return;

        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'guardian_city')) {
                $table->string('guardian_city')->nullable();
            }
            if (!Schema::hasColumn('students', 'guardian_notes')) {
                $table->text('guardian_notes')->nullable();
            }
            if (!Schema::hasColumn('students', 'enroll_date')) {
                $table->date('enroll_date')->nullable();
            }
            if (!Schema::hasColumn('students', 'start_date')) {
                $table->date('start_date')->nullable();
            }
            if (!Schema::hasColumn('students', 'deposit')) {
                $table->decimal('deposit', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('students', 'period')) {
                $table->string('period')->nullable();
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('students')) return;

        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'deposit')) {
                $table->dropColumn('deposit');
            }
            if (Schema::hasColumn('students', 'start_date')) {
                $table->dropColumn('start_date');
            }
            if (Schema::hasColumn('students', 'enroll_date')) {
                $table->dropColumn('enroll_date');
            }
            if (Schema::hasColumn('students', 'guardian_notes')) {
                $table->dropColumn('guardian_notes');
            }
            if (Schema::hasColumn('students', 'guardian_city')) {
                $table->dropColumn('guardian_city');
            }
        });
    }
};
