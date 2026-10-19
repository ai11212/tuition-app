<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One super user sits above the admins: only they may create or modify
     * admin accounts, and only they may read the activity log.
     *
     * Defaults to 0 — the super is appointed deliberately by SQL, never
     * through the interface, so there is no escalation path.
     *
     * Guarded with hasColumn so this is safe to run where the column was
     * already added by hand.
     */
    public function up(): void
    {
        if (Schema::hasColumn('users', 'is_super')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_super')->default(false)->after('is_admin');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'is_super')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_super');
            });
        }
    }
};
