<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(){
        Schema::table('student_attendances', function(Blueprint $t){
            if (!Schema::hasColumn('student_attendances','teacher')) {
                $t->string('teacher')->nullable()->after('subject');
            }
        });
    }
    public function down(){
        Schema::table('student_attendances', function(Blueprint $t){
            if (Schema::hasColumn('student_attendances','teacher')) {
                $t->dropColumn('teacher');
            }
        });
    }
};
