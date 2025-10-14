<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Add deposit_paid boolean field for Yes/No tracking
            // Keep existing deposit field for backward compatibility
            if (!Schema::hasColumn('students', 'deposit_paid')) {
                $table->boolean('deposit_paid')->default(false)->after('deposit');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'deposit_paid')) {
                $table->dropColumn('deposit_paid');
            }
        });
    }
};
