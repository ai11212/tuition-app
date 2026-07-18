<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Staff extends Model
{
    use HasFactory;
    protected $fillable = ['reference','name','phone','email','nationality','address','dbs','dbs_file','hourly_rate','joining_date','leaving_date','reference_doc','reference_file','role','status'];

    protected $casts = ['dbs' => 'boolean', 'reference_doc' => 'boolean', 'joining_date' => 'date', 'leaving_date' => 'date'];
}
