<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Queue extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'patient_id',
        'service_id',
        'priority_id',
        'counter_id',
        'called_by',
        'queue_number',
        'queue_position',
        'status',
        'queue_type',
        'checked_in_at',
        'called_at',
        'serving_started_at',
        'completed_at',
        'estimated_wait_time',
        'actual_wait_time',
        'service_duration',
        'notes',
        'reason_for_visit',
        'recall_count',
        'was_escalated',
        'escalated_at',
        'near_notification_sent',
        'called_notification_sent',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'checked_in_at' => 'datetime',
            'called_at' => 'datetime',
            'serving_started_at' => 'datetime',
            'completed_at' => 'datetime',
            'escalated_at' => 'datetime',
            'was_escalated' => 'boolean',
            'near_notification_sent' => 'boolean',
            'called_notification_sent' => 'boolean',
        ];
    }

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Get the patient for this queue
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the service for this queue
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the priority for this queue
     */
    public function priority(): BelongsTo
    {
        return $this->belongsTo(Priority::class);
    }

    /**
     * Get the counter serving this queue
     */
    public function counter(): BelongsTo
    {
        return $this->belongsTo(Counter::class);
    }

    /**
     * Get the staff who called this queue
     */
    public function calledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'called_by');
    }

    /**
     * Get the staff (alias for calledByUser)
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'called_by');
    }

    /**
     * Get notification logs for this queue
     */
    public function notificationLogs(): HasMany
    {
        return $this->hasMany(NotificationLog::class);
    }

    /**
     * Get the payment associated with the queue
     */
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    // ==========================================
    // QUERY SCOPES
    // ==========================================

    /**
     * Scope to get waiting queues only
     */
    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting');
    }

    /**
     * Scope to get called queues only
     */
    public function scopeCalled($query)
    {
        return $query->where('status', 'called');
    }

    /**
     * Scope to get serving queues only
     */
    public function scopeServing($query)
    {
        return $query->where('status', 'serving');
    }

    /**
     * Scope to get completed queues only
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to get active queues (waiting, called, serving)
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['waiting', 'called', 'serving']);
    }

    /**
     * Scope to get today's queues
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope to filter by service
     */
    public function scopeForService($query, int $serviceId)
    {
        return $query->where('service_id', $serviceId);
    }

    /**
     * Scope to order by priority (highest first) then by creation time
     */
    public function scopePriorityOrdered($query)
    {
        return $query->join('priorities', 'queues.priority_id', '=', 'priorities.id')
            ->orderBy('priorities.level', 'desc')
            ->orderBy('queues.created_at', 'asc')
            ->select('queues.*');
    }

    /**
     * Scope to get queues needing escalation
     */
    public function scopeNeedsEscalation($query)
    {
        return $query->where('status', 'waiting')
            ->where('was_escalated', false)
            ->whereRaw('TIMESTAMPDIFF(MINUTE, created_at, NOW()) > (SELECT max_wait_time FROM priorities WHERE priorities.id = queues.priority_id)');
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Get wait time in minutes
     */
    public function getWaitTimeAttribute(): int
    {
        if ($this->status === 'completed' && $this->actual_wait_time) {
            return $this->actual_wait_time;
        }

        return $this->created_at->diffInMinutes(now());
    }

    /**
     * Get formatted wait time
     */
    public function getFormattedWaitTimeAttribute(): string
    {
        $minutes = $this->wait_time;
        if ($minutes < 60) {
            return "{$minutes} min" . ($minutes !== 1 ? 's' : '');
        }
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        return "{$hours}h {$mins}m";
    }

    /**
     * Get position in queue
     */
    public function getPositionAttribute(): int
    {
        if (!in_array($this->status, ['waiting', 'called'])) {
            return 0;
        }

        return self::where('service_id', $this->service_id)
            ->whereIn('status', ['waiting', 'called'])
            ->whereDate('created_at', today())
            ->where(function ($query) {
                $query->whereHas('priority', function ($q) {
                    $q->where('level', '>', $this->priority->level ?? 0);
                })
                ->orWhere(function ($q) {
                    $q->where('priority_id', $this->priority_id)
                      ->where('created_at', '<', $this->created_at);
                });
            })
            ->count() + 1;
    }

    /**
     * Calculate estimated wait time based on position and average service time
     */
    public function calculateEstimatedWaitTime(): int
    {
        $position = $this->position;
        $averageServiceTime = $this->service->average_service_time ?? 15;
        
        // Factor in number of open counters
        $openCounters = $this->service->counters()->open()->count();
        $openCounters = max(1, $openCounters);
        
        return (int) ceil(($position * $averageServiceTime) / $openCounters);
    }

    /**
     * Check if patient should be notified (3 positions away)
     */
    public function shouldNotifyNear(): bool
    {
        return $this->position <= 3 
            && !$this->near_notification_sent 
            && $this->status === 'waiting';
    }

    /**
     * Call this queue
     */
    public function call(?int $counterId = null, ?int $calledBy = null): bool
    {
        $this->status = 'called';
        $this->called_at = now();
        $this->counter_id = $counterId;
        $this->called_by = $calledBy;
        $this->recall_count++;
        
        return $this->save();
    }

    /**
     * Start serving
     */
    public function startServing(): bool
    {
        $this->status = 'serving';
        $this->serving_started_at = now();
        $this->actual_wait_time = $this->created_at->diffInMinutes(now());
        
        return $this->save();
    }

    /**
     * Complete the queue
     */
    public function complete(): bool
    {
        $this->status = 'completed';
        $this->completed_at = now();
        
        if ($this->serving_started_at) {
            $this->service_duration = $this->serving_started_at->diffInMinutes(now());
        }
        
        return $this->save();
    }

    /**
     * Skip/No show
     */
    public function skip(): bool
    {
        $this->status = 'skipped';
        $this->completed_at = now();
        
        return $this->save();
    }

    /**
     * Cancel the queue
     */
    public function cancel(): bool
    {
        $this->status = 'cancelled';
        $this->completed_at = now();
        
        return $this->save();
    }

    /**
     * Escalate priority
     */
    public function escalate(): bool
    {
        $emergencyPriority = Priority::where('code', 'EMG')->first();
        
        if ($emergencyPriority && $this->priority_id !== $emergencyPriority->id) {
            $this->priority_id = $emergencyPriority->id;
            $this->was_escalated = true;
            $this->escalated_at = now();
            return $this->save();
        }
        
        return false;
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'waiting' => 'bg-warning text-dark',
            'called' => 'bg-info',
            'serving' => 'bg-primary',
            'completed' => 'bg-success',
            'skipped' => 'bg-secondary',
            'cancelled' => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    /**
     * Get status display text
     */
    public function getStatusTextAttribute(): string
    {
        return match($this->status) {
            'waiting' => 'Waiting',
            'called' => 'Called',
            'serving' => 'Being Served',
            'completed' => 'Completed',
            'skipped' => 'Skipped',
            'cancelled' => 'Cancelled',
            default => 'Unknown',
        };
    }

    /**
     * Check if queue can be called
     */
    public function canBeCalled(): bool
    {
        return $this->status === 'waiting';
    }

    /**
     * Check if queue can be served
     */
    public function canBeServed(): bool
    {
        return $this->status === 'called';
    }

    /**
     * Check if queue can be completed
     */
    public function canBeCompleted(): bool
    {
        return in_array($this->status, ['serving', 'called']);
    }
}
