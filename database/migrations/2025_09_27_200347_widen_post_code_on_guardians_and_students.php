<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Use raw SQL so we don't require doctrine/dbal for change()
        if (Schema::hasColumn('guardians', 'post_code')) {
            DB::statement("ALTER TABLE `guardians` MODIFY COLUMN `post_code` VARCHAR(12) NULL");
        }
        if (Schema::hasColumn('students', 'post_code')) {
            DB::statement("ALTER TABLE `students` MODIFY COLUMN `post_code` VARCHAR(12) NULL");
        }
    }

    public function down(): void
    {
        // Revert to VARCHAR(8) if you previously used 8. Adjust if needed.
        if (Schema::hasColumn('guardians', 'post_code')) {
            DB::statement("ALTER TABLE `guardians` MODIFY COLUMN `post_code` VARCHAR(8) NULL");
        }
        if (Schema::hasColumn('students', 'post_code')) {
            DB::statement("ALTER TABLE `students` MODIFY COLUMN `post_code` VARCHAR(8) NULL");
        }
    }
};
