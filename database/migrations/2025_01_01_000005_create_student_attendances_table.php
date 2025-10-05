<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(){ Schema::create('student_attendances', function(Blueprint $t){
        $t->id();
        $t->foreignId('student_id')->constrained()->cascadeOnDelete();
        $t->date('date');
        $t->string('status');
        $t->string('subject')->nullable();
        $t->string('time')->nullable();
        $t->timestamps();
        $t->unique(['student_id','date','subject']);
    }); }
    public function down(){ Schema::dropIfExists('student_attendances'); }
};
