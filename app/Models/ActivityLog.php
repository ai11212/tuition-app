<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    /** Rows are written once and never updated, so only created_at is kept */
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'user_name', 'action', 'description', 'method', 'url', 'ip', 'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * When the action happened, in display (UK) time.
     *
     * Unlike a payment there is no separate business date here — created_at is
     * the moment itself, so the whole value converts.
     */
    public function getLoggedAtAttribute()
    {
        return \App\Support\DisplayTime::local($this->created_at);
    }
}
