<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Counter extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'service_id',
        'name',
        'code',
        'assigned_staff_id',
        'status',
        'location',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Get the service this counter belongs to
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the staff assigned to this counter
     */
    public function assignedStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_staff_id');
    }

    /**
     * Get all queues served at this counter
     */
    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class);
    }

    // ==========================================
    // QUERY SCOPES
    // ==========================================

    /**
     * Scope to get active counters only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get open counters only
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    /**
     * Scope to filter by service
     */
    public function scopeForService($query, int $serviceId)
    {
        return $query->where('service_id', $serviceId);
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Check if counter is open
     */
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    /**
     * Check if counter is currently serving
     */
    public function isServing(): bool
    {
        return $this->queues()
            ->where('status', 'serving')
            ->whereDate('created_at', today())
            ->exists();
    }

    /**
     * Get currently serving queue
     */
    public function getCurrentServingAttribute(): ?Queue
    {
        return $this->queues()
            ->where('status', 'serving')
            ->whereDate('created_at', today())
            ->first();
    }

    /**
     * Get today's served count
     */
    public function getTodayServedCountAttribute(): int
    {
        return $this->queues()
            ->where('status', 'completed')
            ->whereDate('created_at', today())
            ->count();
    }

    /**
     * Open the counter
     */
    public function open(): bool
    {
        $this->status = 'open';
        return $this->save();
    }

    /**
     * Close the counter
     */
    public function close(): bool
    {
        $this->status = 'closed';
        return $this->save();
    }

    /**
     * Set counter on break
     */
    public function takeBreak(): bool
    {
        $this->status = 'break';
        return $this->save();
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'open' => 'bg-success',
            'break' => 'bg-warning text-dark',
            default => 'bg-secondary',
        };
    }
}
