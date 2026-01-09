<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Priority extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'code',
        'description',
        'level',
        'color',
        'icon',
        'max_wait_time',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'level' => 'integer',
            'max_wait_time' => 'integer',
        ];
    }

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Get all queues with this priority
     */
    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class);
    }

    // ==========================================
    // QUERY SCOPES
    // ==========================================

    /**
     * Scope to get active priorities only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by priority level (highest first)
     */
    public function scopeByLevel($query)
    {
        return $query->orderBy('level', 'desc');
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Check if this is emergency priority
     */
    public function isEmergency(): bool
    {
        return $this->code === 'EMG';
    }

    /**
     * Check if this is senior priority
     */
    public function isSenior(): bool
    {
        return $this->code === 'SNR';
    }

    /**
     * Get badge class for styling
     */
    public function getBadgeClassAttribute(): string
    {
        return match($this->code) {
            'EMG' => 'bg-danger',
            'SNR' => 'bg-warning text-dark',
            'PWD' => 'bg-info',
            default => 'bg-secondary',
        };
    }
}
