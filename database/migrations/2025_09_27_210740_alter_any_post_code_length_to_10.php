<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // We will try these common tables; guarded by Schema checks.
        $candidates = ['guardians','students','parents','contacts','users'];
        foreach ($candidates as $t) {
            if (Schema::hasTable($t) && Schema::hasColumn($t, 'post_code')) {
                // Make it wide enough for "E1 6AN" etc.
                DB::statement("ALTER TABLE `{$t}` MODIFY `post_code` VARCHAR(10)");
            }
        }
    }

    public function down(): void
    {
        // Best-effort revert to 6 (adjust if your original length was different).
        $candidates = ['guardians','students','parents','contacts','users'];
        foreach ($candidates as $t) {
            if (Schema::hasTable($t) && Schema::hasColumn($t, 'post_code')) {
                DB::statement("ALTER TABLE `{$t}` MODIFY `post_code` VARCHAR(6)");
            }
        }
    }
};
