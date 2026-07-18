<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class LibraryBook extends Model {
    protected $fillable = ['subject','title','price'];
}
