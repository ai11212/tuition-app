<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(){ Schema::create('timetables', function(Blueprint $t){
        $t->id();
        $t->string('student_reference');
        $t->string('day_of_week');
        $t->time('start_time');
        $t->time('end_time');
        $t->string('subject');
        $t->string('teacher_name')->nullable();
        $t->string('room')->nullable();
        $t->timestamps();
    }); }
    public function down(){ Schema::dropIfExists('timetables'); }
};
