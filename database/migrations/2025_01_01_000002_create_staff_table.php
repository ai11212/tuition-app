<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(){ Schema::create('staff', function(Blueprint $t){
        $t->id();
        $t->string('name');
        $t->string('phone')->nullable();
        $t->string('email')->nullable();
        $t->string('role')->default('teacher');
        $t->string('status')->default('active');
        $t->timestamps();
    }); }
    public function down(){ Schema::dropIfExists('staff'); }
};
