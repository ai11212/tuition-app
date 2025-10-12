<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Book extends Model
{
    use HasFactory;
    protected $fillable = ['reference','subject','title','price','student_reference'];
    
    // Relationship to student
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_reference', 'reference');
    }
}
