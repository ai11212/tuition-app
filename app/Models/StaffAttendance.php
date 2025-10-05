<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StaffAttendance extends Model
{
    use HasFactory;
    protected $fillable = ['staff_id','date','status','time'];
    public function staff(){ return $this->belongsTo(Staff::class); }
}
