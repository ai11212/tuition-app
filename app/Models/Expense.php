<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model {
    protected $fillable = ['expense_on','amount','method','type','category','notes'];
}
