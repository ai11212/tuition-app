<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(){
        Schema::table('students', function (Blueprint $t) {
            if (!Schema::hasColumn('students','guardian_name'))    $t->string('guardian_name')->nullable();
            if (!Schema::hasColumn('students','guardian_phone'))   $t->string('guardian_phone')->nullable();
            if (!Schema::hasColumn('students','guardian_email'))   $t->string('guardian_email')->nullable();
            if (!Schema::hasColumn('students','city'))             $t->string('city')->nullable();
            if (!Schema::hasColumn('students','notes'))            $t->text('notes')->nullable();

            if (!Schema::hasColumn('students','dob'))              $t->date('dob')->nullable();
            if (!Schema::hasColumn('students','enroll_date'))      $t->date('enroll_date')->nullable();
            if (!Schema::hasColumn('students','gender'))           $t->string('gender',16)->nullable();
            if (!Schema::hasColumn('students','fee_amount'))       $t->decimal('fee_amount',10,2)->nullable();
            if (!Schema::hasColumn('students','start_date'))       $t->date('start_date')->nullable();
            if (!Schema::hasColumn('students','period'))           $t->string('period',32)->nullable();
            if (!Schema::hasColumn('students','full_time'))        $t->boolean('full_time')->default(false);

            if (!Schema::hasColumn('students','lesson1'))          $t->string('lesson1')->nullable();
            if (!Schema::hasColumn('students','lesson2'))          $t->string('lesson2')->nullable();
            if (!Schema::hasColumn('students','lesson3'))          $t->string('lesson3')->nullable();
            if (!Schema::hasColumn('students','lesson4'))          $t->string('lesson4')->nullable();

            if (!Schema::hasColumn('students','deposit'))          $t->decimal('deposit',10,2)->nullable();
        });
    }
    public function down(){
        Schema::table('students', function (Blueprint $t) {
            foreach (['guardian_name','guardian_phone','guardian_email','city','notes','dob','enroll_date','gender','fee_amount','start_date','period','full_time','lesson1','lesson2','lesson3','lesson4','deposit'] as $col) {
                if (Schema::hasColumn('students',$col)) $t->dropColumn($col);
            }
        });
    }
};
