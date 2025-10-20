<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentTransaction extends Model
{
    use HasFactory;
    protected $fillable = ['invoice_id','paid_on','paid_at','amount','method','notes','purpose'];
    public function invoice(){ return $this->belongsTo(Invoice::class); }
}
