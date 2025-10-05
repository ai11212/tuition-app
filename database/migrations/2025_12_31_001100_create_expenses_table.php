<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(){
        Schema::create('expenses', function (Blueprint $t) {
            $t->id();
            $t->date('expense_on')->index();
            $t->decimal('amount', 10, 2);       // positive number
            $t->string('method', 20)->nullable(); // Cash | Card | Bank
            $t->string('type', 20)->default('expense'); // expense | refund
            $t->string('category', 50)->nullable();
            $t->string('notes', 255)->nullable();
            $t->timestamps();
        });
    }
    public function down(){ Schema::dropIfExists('expenses'); }
};
