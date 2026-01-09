<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /**
     * Get the user who performed the action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the related model
     */
    public function model()
    {
        if ($this->model_type && $this->model_id) {
            return $this->model_type::find($this->model_id);
        }
        return null;
    }

    /**
     * Log an action
     */
    public static function log(string $action, string $description, $model = null, array $oldValues = null, array $newValues = null): self
    {
        return self::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model?->id,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Get action badge class
     */
    public function getActionBadgeAttribute(): string
    {
        return match($this->action) {
            'login' => 'bg-success',
            'logout' => 'bg-secondary',
            'create' => 'bg-primary',
            'update' => 'bg-info',
            'delete' => 'bg-danger',
            'queue_call' => 'bg-warning text-dark',
            'queue_complete' => 'bg-success',
            'queue_skip' => 'bg-secondary',
            default => 'bg-light text-dark',
        };
    }

    /**
     * Get action icon
     */
    public function getActionIconAttribute(): string
    {
        return match($this->action) {
            'login' => 'fa-sign-in-alt',
            'logout' => 'fa-sign-out-alt',
            'create' => 'fa-plus',
            'update' => 'fa-edit',
            'delete' => 'fa-trash',
            'queue_call' => 'fa-phone',
            'queue_complete' => 'fa-check',
            'queue_skip' => 'fa-forward',
            default => 'fa-info-circle',
        };
    }
}
