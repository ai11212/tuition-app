<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(){ Schema::create('staff_attendances', function(Blueprint $t){
        $t->id();
        $t->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
        $t->date('date');
        $t->string('status');
        $t->string('time')->nullable();
        $t->timestamps();
        $t->unique(['staff_id','date']);
    }); }
    public function down(){ Schema::dropIfExists('staff_attendances'); }
};
