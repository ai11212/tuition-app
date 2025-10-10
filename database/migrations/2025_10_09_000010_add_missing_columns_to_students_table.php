<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(){
        Schema::table('students', function(Blueprint $t){
            $t->string('gender')->nullable();
            $t->date('dob')->nullable();
            // $t->string('guardian_name')->nullable(); // Already exists, prevent duplicate column error
            if (!Schema::hasColumn('students', 'guardian_city')) $t->string('guardian_city')->nullable();
            if (!Schema::hasColumn('students', 'enroll_date')) $t->date('enroll_date')->nullable();
            if (!Schema::hasColumn('students', 'start_date')) $t->date('start_date')->nullable();
            // deposit already guarded elsewhere
            if (!Schema::hasColumn('students', 'period')) $t->string('period')->nullable();
        });
    }
    public function down(){
        Schema::table('students', function(Blueprint $t){
            $t->dropColumn([
                'gender',
                'dob',
                'guardian_name',
                'guardian_city',
                'enroll_date',
                'start_date',
                'deposit',
                'period'
            ]);
        });
    }
};
