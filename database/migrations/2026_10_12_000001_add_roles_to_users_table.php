<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds per-user access control. Guarded with hasColumn so this is safe to
     * run on a database where the columns were already added by hand (the live
     * server gets them via SQL before the code is deployed).
     *
     * is_admin defaults to 1 deliberately: when the column appears on a live
     * database the existing account must keep full access, or everyone is
     * locked out. New accounts are given an explicit role by the Users screen.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_admin')) {
                $table->boolean('is_admin')->default(true)->after('email');
            }
            if (!Schema::hasColumn('users', 'capabilities')) {
                $table->text('capabilities')->nullable()->after('is_admin');
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('capabilities');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['is_admin', 'capabilities', 'is_active'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
