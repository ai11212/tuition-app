<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(){
        Schema::table('students', function (Blueprint $t) {
            // drop the unique index created earlier: students_reference_unique
            try { $t->dropUnique('students_reference_unique'); } catch (\Throwable $e) {}
            // add a normal index for faster lookups
            if (!Schema::hasColumn('students', 'reference')) return;
            try { $t->index('reference'); } catch (\Throwable $e) {}
        });
    }
    public function down(){
        Schema::table('students', function (Blueprint $t) {
            try { $t->dropIndex(['reference']); } catch (\Throwable $e) {}
            try { $t->unique('reference'); } catch (\Throwable $e) {}
        });
    }
};
