<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'code',
        'description',
        'icon',
        'color',
        'average_service_time',
        'max_queue_size',
        'is_active',
        'display_order',
        'price',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'average_service_time' => 'integer',
            'max_queue_size' => 'integer',
            'display_order' => 'integer',
        ];
    }

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Get all queues for this service
     */
    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class);
    }

    /**
     * Get all counters for this service
     */
    public function counters(): HasMany
    {
        return $this->hasMany(Counter::class);
    }

    /**
     * Get staff assigned to this service
     */
    public function staff(): HasMany
    {
        return $this->hasMany(User::class, 'assigned_service_id');
    }

    // ==========================================
    // QUERY SCOPES
    // ==========================================

    /**
     * Scope to get active services only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by display order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Get current waiting queue count
     */
    public function getWaitingCountAttribute(): int
    {
        return $this->queues()
            ->whereIn('status', ['waiting', 'called'])
            ->whereDate('created_at', today())
            ->count();
    }

    /**
     * Get current serving queue
     */
    public function getCurrentServingAttribute(): ?Queue
    {
        return $this->queues()
            ->where('status', 'serving')
            ->whereDate('created_at', today())
            ->first();
    }

    /**
     * Get today's completed count
     */
    public function getTodayCompletedCountAttribute(): int
    {
        return $this->queues()
            ->where('status', 'completed')
            ->whereDate('created_at', today())
            ->count();
    }

    /**
     * Calculate average wait time for today
     */
    public function getAverageWaitTimeAttribute(): int
    {
        return (int) $this->queues()
            ->where('status', 'completed')
            ->whereDate('created_at', today())
            ->whereNotNull('actual_wait_time')
            ->avg('actual_wait_time') ?? 0;
    }

    /**
     * Check if queue is full
     */
    public function isQueueFull(): bool
    {
        return $this->waiting_count >= $this->max_queue_size;
    }

    /**
     * Generate next queue number
     */
    public function generateQueueNumber(): string
    {
        $todayCount = $this->queues()
            ->whereDate('created_at', today())
            ->count() + 1;

        return sprintf('%s-%03d', $this->code, $todayCount);
    }
}
