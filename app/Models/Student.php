<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'reference',
        'first_name','last_name','gender','dob',
        'guardian_name','guardian_relation','guardian_phone','guardian_email','guardian_address',
        'guardian_city',
        'post_code',
        'city', 'enroll_date', 'start_date', 'deposit', 'deposit_paid', 'payment', 'payment_plan', 'period',
    ];

    // Safety net: auto-assign reference if controller missed it (should not happen now)
    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->reference)) {
                $nextId = (int) (self::max('id') ?? 0) + 1;
                $model->reference = 'A' . date('ymd') . str_pad((string)$nextId, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * Normalize and cap post_code (guardian post code) to 120 chars, multibyte-safe.
     */
    public function setPostCodeAttribute($value)
    {
        $v = trim((string) $value);
        $this->attributes['post_code'] = function_exists('mb_substr')
            ? (mb_substr($v, 0, 120) ?: null)
            : (substr($v, 0, 120) ?: null);
    }
}
