<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Schema;

class Book extends Model
{
    use HasFactory;
    
    // Dynamic fillable array based on column existence
    public function getFillable()
    {
        $fillable = ['reference','subject','title','price'];
        
        // Add student_reference only if column exists
        if (Schema::hasColumn('books', 'student_reference')) {
            $fillable[] = 'student_reference';
        }
        
        return $fillable;
    }
    
    protected $fillable = ['reference','subject','title','price'];
    
    // Override to use dynamic fillable
    public function fill(array $attributes)
    {
        $this->fillable = $this->getFillable();
        return parent::fill($attributes);
    }
    
    // Relationship to student (only if column exists)
    public function student()
    {
        if (Schema::hasColumn('books', 'student_reference')) {
            return $this->belongsTo(Student::class, 'student_reference', 'reference');
        }
        return null;
    }
}
