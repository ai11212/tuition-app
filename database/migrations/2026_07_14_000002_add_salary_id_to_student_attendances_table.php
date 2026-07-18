<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations. NULL = not yet included in a teacher salary payment.
     * Deliberately NOT in StudentAttendance::$fillable — attendance flows can
     * never touch it; only the salary module writes it explicitly.
     */
    public function up(): void
    {
        Schema::table('student_attendances', function (Blueprint $table) {
            $table->unsignedBigInteger('salary_id')->nullable()->index()->after('teacher');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_attendances', function (Blueprint $table) {
            $table->dropColumn('salary_id');
        });
    }
};
