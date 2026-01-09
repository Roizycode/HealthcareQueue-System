<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class QueueSetting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
        'group',
    ];

    /**
     * Cache key for settings
     */
    protected static string $cacheKey = 'queue_settings';

    // ==========================================
    // STATIC METHODS
    // ==========================================

    /**
     * Get a setting value by key
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $settings = self::getAllCached();
        
        if (!isset($settings[$key])) {
            return $default;
        }

        $setting = $settings[$key];
        return self::castValue($setting['value'], $setting['type']);
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, mixed $value, string $type = 'string', ?string $description = null, string $group = 'general'): void
    {
        self::updateOrCreate(
            ['key' => $key],
            [
                'value' => is_array($value) ? json_encode($value) : (string) $value,
                'type' => $type,
                'description' => $description,
                'group' => $group,
            ]
        );

        self::clearCache();
    }

    /**
     * Get all settings cached
     */
    public static function getAllCached(): array
    {
        return Cache::remember(self::$cacheKey, 3600, function () {
            return self::all()->keyBy('key')->map(function ($setting) {
                return [
                    'value' => $setting->value,
                    'type' => $setting->type,
                ];
            })->toArray();
        });
    }

    /**
     * Clear the settings cache
     */
    public static function clearCache(): void
    {
        Cache::forget(self::$cacheKey);
    }

    /**
     * Cast value to the appropriate type
     */
    protected static function castValue(mixed $value, string $type): mixed
    {
        return match($type) {
            'integer', 'int' => (int) $value,
            'float', 'double' => (float) $value,
            'boolean', 'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json', 'array' => json_decode($value, true),
            default => $value,
        };
    }

    // ==========================================
    // QUERY SCOPES
    // ==========================================

    /**
     * Scope to filter by group
     */
    public function scopeGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    // ==========================================
    // DEFAULT SETTINGS
    // ==========================================

    /**
     * Get default settings
     */
    public static function getDefaults(): array
    {
        return [
            // General
            'clinic_name' => ['value' => 'HealthCare Queue System', 'type' => 'string', 'group' => 'general'],
            'clinic_address' => ['value' => '123 Medical Center Drive', 'type' => 'string', 'group' => 'general'],
            'clinic_phone' => ['value' => '+1234567890', 'type' => 'string', 'group' => 'general'],
            
            // Queue Settings
            'queue_near_threshold' => ['value' => '3', 'type' => 'integer', 'group' => 'queue', 'description' => 'Notify patient when X positions away'],
            'max_recall_attempts' => ['value' => '3', 'type' => 'integer', 'group' => 'queue', 'description' => 'Max times to call before skipping'],
            'auto_skip_after_minutes' => ['value' => '10', 'type' => 'integer', 'group' => 'queue', 'description' => 'Auto-skip after X minutes of no response'],
            'escalation_enabled' => ['value' => 'true', 'type' => 'boolean', 'group' => 'queue'],
            
            // Notification Settings
            'sms_enabled' => ['value' => 'true', 'type' => 'boolean', 'group' => 'notifications'],
            'email_enabled' => ['value' => 'true', 'type' => 'boolean', 'group' => 'notifications'],
            'sms_near_message' => ['value' => 'Your queue number {queue_number} is almost up! You are position {position} in line.', 'type' => 'string', 'group' => 'notifications'],
            'sms_called_message' => ['value' => 'Your queue number {queue_number} is now being called! Please proceed to {counter}.', 'type' => 'string', 'group' => 'notifications'],
            
            // Operating Hours
            'operating_hours_start' => ['value' => '08:00', 'type' => 'string', 'group' => 'hours'],
            'operating_hours_end' => ['value' => '17:00', 'type' => 'string', 'group' => 'hours'],
            'operating_days' => ['value' => '[1,2,3,4,5]', 'type' => 'json', 'group' => 'hours', 'description' => 'Days of week (1=Monday, 7=Sunday)'],
        ];
    }

    /**
     * Seed default settings
     */
    public static function seedDefaults(): void
    {
        foreach (self::getDefaults() as $key => $config) {
            if (!self::where('key', $key)->exists()) {
                self::create([
                    'key' => $key,
                    'value' => $config['value'],
                    'type' => $config['type'],
                    'description' => $config['description'] ?? null,
                    'group' => $config['group'],
                ]);
            }
        }
        self::clearCache();
    }
}
