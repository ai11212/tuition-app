<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentReminder extends Model
{
    protected $fillable = [
        'student_id',
        'reference',
        'student_names',
        'expected_amount',
        'last_payment_date',
        'last_payment_amount',
        'days_since_payment',
        'next_due_date',
        'period',
        'status',
        'priority',
        'is_holiday',
        'notes',
        'calculated_at',
    ];

    protected $casts = [
        'student_names' => 'array',
        'expected_amount' => 'decimal:2',
        'last_payment_amount' => 'decimal:2',
        'last_payment_date' => 'date',
        'next_due_date' => 'date',
        'is_holiday' => 'boolean',
        'calculated_at' => 'datetime',
    ];

    /**
     * Get the student that owns the reminder
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Scope for overdue reminders
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue')
                     ->where('is_holiday', false)
                     ->orderBy('days_since_payment', 'desc');
    }

    /**
     * Scope for due reminders
     */
    public function scopeDue($query)
    {
        return $query->where('status', 'due')
                     ->where('is_holiday', false)
                     ->orderBy('days_since_payment', 'desc');
    }

    /**
     * Scope for active (not on holiday)
     */
    public function scopeActive($query)
    {
        return $query->where('is_holiday', false);
    }

    /**
     * Get formatted student names
     */
    public function getFormattedNamesAttribute(): string
    {
        if (empty($this->student_names)) {
            return '';
        }

        return is_array($this->student_names) 
            ? implode(', ', $this->student_names)
            : $this->student_names;
    }

    /**
     * Get days overdue text
     */
    public function getDaysOverdueTextAttribute(): string
    {
        if ($this->days_since_payment == 0) {
            return 'Never paid';
        }

        return $this->days_since_payment . ' days ago';
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'overdue' => 'bg-danger',
            'due' => 'bg-warning',
            default => 'bg-secondary',
        };
    }
}
