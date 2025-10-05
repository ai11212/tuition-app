<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(){ Schema::create('payment_transactions', function(Blueprint $t){
        $t->id();
        $t->foreignId('invoice_id')->constrained()->cascadeOnDelete();
        $t->date('paid_on');
        $t->decimal('amount',10,2);
        $t->string('method');
        $t->text('notes')->nullable();
        $t->timestamps();
    }); }
    public function down(){ Schema::dropIfExists('payment_transactions'); }
};
