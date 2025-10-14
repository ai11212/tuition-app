<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(){
        Schema::table('students', function(Blueprint $t){
            if (!Schema::hasColumn('students', 'guardian_name')) $t->string('guardian_name')->nullable();
            if (!Schema::hasColumn('students', 'guardian_city')) $t->string('guardian_city')->nullable();
            if (!Schema::hasColumn('students', 'enroll_date')) $t->date('enroll_date')->nullable();
            if (!Schema::hasColumn('students', 'start_date')) $t->date('start_date')->nullable();
            if (!Schema::hasColumn('students', 'deposit')) $t->decimal('deposit', 10, 2)->nullable();
            if (!Schema::hasColumn('students', 'period')) $t->string('period')->nullable();
            if (!Schema::hasColumn('students', 'dob')) $t->date('dob')->nullable();
            if (!Schema::hasColumn('students', 'gender')) $t->string('gender')->nullable();
            if (!Schema::hasColumn('students', 'post_code')) $t->string('post_code', 20)->nullable();
        });
    }
    public function down(){
        Schema::table('students', function(Blueprint $t){
            foreach ([
                'guardian_name', 'guardian_city', 'enroll_date', 'start_date', 'deposit', 'period', 'dob', 'gender', 'post_code'
            ] as $col) {
                if (Schema::hasColumn('students', $col)) $t->dropColumn($col);
            }
        });
    }
};
