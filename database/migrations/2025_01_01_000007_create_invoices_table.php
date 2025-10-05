<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(){ Schema::create('invoices', function(Blueprint $t){
        $t->id();
        $t->foreignId('student_id')->constrained()->cascadeOnDelete();
        $t->string('reference');
        $t->date('period_from');
        $t->date('period_to');
        $t->decimal('amount',10,2);
        $t->decimal('balance',10,2)->default(0);
        $t->string('status')->default('paid');
        $t->timestamps();
    }); }
    public function down(){ Schema::dropIfExists('invoices'); }
};
