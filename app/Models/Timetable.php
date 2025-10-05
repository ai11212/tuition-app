<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Timetable extends Model {
    protected $fillable = [
        'student_reference','day_of_week','start_time','end_time','subject','teacher_name','room'
    ];
    public $timestamps = false;
}
