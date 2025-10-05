<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(){ Schema::create('students', function(Blueprint $t){
        $t->id();
        $t->string('reference')->unique();
        $t->string('first_name');
        $t->string('last_name')->nullable();
        $t->string('phone')->nullable();
        $t->string('email')->nullable();
        $t->string('address')->nullable();
        $t->string('status')->default('active');
        $t->timestamps();
    }); }
    public function down(){ Schema::dropIfExists('students'); }
};
