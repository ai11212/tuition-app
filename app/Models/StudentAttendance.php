<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StudentAttendance extends Model
{
    use HasFactory;
    protected $fillable = ['student_id','date','status','subject','time'];
    public function student(){ return $this->belongsTo(Student::class); }
}
