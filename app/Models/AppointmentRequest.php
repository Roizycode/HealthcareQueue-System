<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppointmentRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'service_id',
        'preferred_date',
        'preferred_time',
        'notes',
        'status',
        'handled_by',
        'staff_notes',
        'handled_at',
    ];

    protected $casts = [
        'preferred_date' => 'date',
        'handled_at' => 'datetime',
    ];

    /**
     * Get the patient that made the request
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the service requested
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the staff who handled the request
     */
    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    /**
     * Scope for pending requests
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved requests
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Get formatted preferred time
     */
    public function getFormattedTimeAttribute(): string
    {
        return match($this->preferred_time) {
            'morning' => 'Morning (8AM - 12PM)',
            'afternoon' => 'Afternoon (1PM - 5PM)',
            default => $this->preferred_time ?? 'Any time',
        };
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'cancelled' => 'secondary',
            default => 'secondary',
        };
    }
}
