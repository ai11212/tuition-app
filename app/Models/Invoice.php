<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory;
    protected $fillable = ['student_id','reference','period_from','period_to','amount','balance','due_added','status'];
    public function student(){ return $this->belongsTo(Student::class); }
    public function transactions(){ return $this->hasMany(PaymentTransaction::class); }
}
