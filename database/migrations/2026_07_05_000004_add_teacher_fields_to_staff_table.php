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
        Schema::table('staff', function (Blueprint $table) {
            $table->string('reference', 20)->nullable()->index()->after('id');
            $table->string('nationality', 100)->nullable()->after('email');
            $table->string('address', 190)->nullable()->after('nationality');
            $table->boolean('dbs')->default(false)->after('address');
            $table->decimal('hourly_rate', 8, 2)->nullable()->after('dbs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropColumn(['reference', 'nationality', 'address', 'dbs', 'hourly_rate']);
        });
    }
};
