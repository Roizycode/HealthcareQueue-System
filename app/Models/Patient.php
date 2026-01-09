<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Notifications\Notifiable;

class Patient extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'patient_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'gender',
        'address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'medical_notes',
        'is_senior',
        'is_pwd',
        'sms_notifications',
        'email_notifications',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'is_senior' => 'boolean',
            'is_pwd' => 'boolean',
            'sms_notifications' => 'boolean',
            'email_notifications' => 'boolean',
        ];
    }

    // ==========================================
    // BOOT
    // ==========================================

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($patient) {
            if (empty($patient->patient_id)) {
                $patient->patient_id = self::generatePatientId();
            }
        });
    }

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Get the user account associated with this patient
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all queues for this patient
     */
    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class);
    }

    /**
     * Get notification logs for this patient
     */
    public function notificationLogs(): HasMany
    {
        return $this->hasMany(NotificationLog::class);
    }

    // ==========================================
    // QUERY SCOPES
    // ==========================================

    /**
     * Scope to search by name, email, or phone
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('first_name', 'like', "%{$term}%")
              ->orWhere('last_name', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%")
              ->orWhere('phone', 'like', "%{$term}%")
              ->orWhere('patient_id', 'like', "%{$term}%");
        });
    }

    /**
     * Scope to get senior patients
     */
    public function scopeSeniors($query)
    {
        return $query->where('is_senior', true);
    }

    /**
     * Scope to get PWD patients
     */
    public function scopePwd($query)
    {
        return $query->where('is_pwd', true);
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Get full name
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Get age from date of birth
     */
    public function getAgeAttribute(): ?int
    {
        if (!$this->date_of_birth) {
            return null;
        }
        return $this->date_of_birth->age;
    }

    /**
     * Check if patient has active queue
     */
    public function hasActiveQueue(): bool
    {
        return $this->queues()
            ->whereIn('status', ['waiting', 'called', 'serving'])
            ->whereDate('created_at', today())
            ->exists();
    }

    /**
     * Get active queue
     */
    public function getActiveQueueAttribute(): ?Queue
    {
        return $this->queues()
            ->whereIn('status', ['waiting', 'called', 'serving'])
            ->whereDate('created_at', today())
            ->first();
    }

    /**
     * Determine recommended priority based on patient attributes
     */
    public function getRecommendedPriorityAttribute(): ?Priority
    {
        if ($this->is_senior) {
            return Priority::where('code', 'SNR')->first();
        }
        if ($this->is_pwd) {
            return Priority::where('code', 'PWD')->first();
        }
        return Priority::where('code', 'REG')->first();
    }

    /**
     * Generate unique patient ID
     */
    public static function generatePatientId(): string
    {
        do {
            $id = 'PAT-' . strtoupper(substr(uniqid(), -6));
        } while (self::where('patient_id', $id)->exists());

        return $id;
    }

    /**
     * Get initials for avatar
     */
    public function getInitialsAttribute(): string
    {
        return strtoupper(substr($this->first_name, 0, 1) . substr($this->last_name, 0, 1));
    }
}
