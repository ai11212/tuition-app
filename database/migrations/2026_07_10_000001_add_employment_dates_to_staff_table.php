<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations. (The status column already exists on staff with
     * default 'active' — only the employment dates are new.)
     */
    public function up(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->date('joining_date')->nullable()->after('hourly_rate');
            $table->date('leaving_date')->nullable()->after('joining_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropColumn(['joining_date', 'leaving_date']);
        });
    }
};
