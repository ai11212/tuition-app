<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(){ Schema::create('books', function(Blueprint $t){
        $t->id();
        $t->string('reference');
        $t->string('subject');
        $t->string('title');
        $t->decimal('price',8,2);
        $t->timestamps();
    }); }
    public function down(){ Schema::dropIfExists('books'); }
};
