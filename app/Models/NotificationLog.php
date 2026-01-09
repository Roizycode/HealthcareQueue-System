<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'queue_id',
        'patient_id',
        'type',
        'notification_type',
        'recipient',
        'message',
        'subject',
        'status',
        'provider',
        'provider_message_id',
        'error_message',
        'cost',
        'retry_count',
        'sent_at',
        'delivered_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'cost' => 'decimal:4',
            'sent_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Get the queue for this notification
     */
    public function queue(): BelongsTo
    {
        return $this->belongsTo(Queue::class);
    }

    /**
     * Get the patient for this notification
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    // ==========================================
    // QUERY SCOPES
    // ==========================================

    /**
     * Scope to get pending notifications
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get sent notifications
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope to get failed notifications
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope to get SMS notifications
     */
    public function scopeSms($query)
    {
        return $query->where('type', 'sms');
    }

    /**
     * Scope to get email notifications
     */
    public function scopeEmail($query)
    {
        return $query->where('type', 'email');
    }

    /**
     * Scope to filter by notification type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('notification_type', $type);
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Mark as sent
     */
    public function markAsSent(?string $messageId = null): bool
    {
        $this->status = 'sent';
        $this->sent_at = now();
        $this->provider_message_id = $messageId;
        
        return $this->save();
    }

    /**
     * Mark as delivered
     */
    public function markAsDelivered(): bool
    {
        $this->status = 'delivered';
        $this->delivered_at = now();
        
        return $this->save();
    }

    /**
     * Mark as failed
     */
    public function markAsFailed(string $errorMessage): bool
    {
        $this->status = 'failed';
        $this->error_message = $errorMessage;
        $this->retry_count++;
        
        return $this->save();
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'pending' => 'bg-warning text-dark',
            'sent' => 'bg-info',
            'delivered' => 'bg-success',
            'failed' => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    /**
     * Get type icon
     */
    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            'sms' => 'fas fa-sms',
            'email' => 'fas fa-envelope',
            'push' => 'fas fa-bell',
            default => 'fas fa-comment',
        };
    }
}
