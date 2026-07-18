<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TeacherSalary extends Model
{
    protected $fillable = [
        'reference', 'staff_id', 'teacher_name', 'date_from', 'date_to',
        'hourly_rate', 'sessions', 'hours', 'gross',
        'payment_method', 'payment_date', 'notes', 'status', 'expense_id',
    ];

    protected $casts = [
        'date_from'    => 'date',
        'date_to'      => 'date',
        'payment_date' => 'date',
    ];
}
