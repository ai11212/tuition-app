<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations. Teacher name / rate / totals are snapshotted so
     * salary history stays immutable even if the teacher record changes.
     */
    public function up(): void
    {
        Schema::create('teacher_salaries', function (Blueprint $t) {
            $t->id();
            $t->string('reference', 20)->index();      // SAL001, SAL002, ...
            $t->unsignedBigInteger('staff_id')->index();
            $t->string('teacher_name');
            $t->date('date_from');
            $t->date('date_to');
            $t->decimal('hourly_rate', 8, 2);
            $t->unsignedInteger('sessions');
            $t->unsignedInteger('hours');
            $t->decimal('gross', 10, 2);
            $t->string('payment_method', 20);          // Cash | Card | Bank
            $t->date('payment_date');
            $t->string('notes', 255)->nullable();
            $t->string('status', 20)->default('paid');
            $t->unsignedBigInteger('expense_id')->nullable()->index(); // linked auto-created expense
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_salaries');
    }
};
