<?php

namespace App\Services;

use App\Models\Queue;
use App\Models\Patient;
use App\Models\NotificationLog;
use App\Models\QueueSetting;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client as TwilioClient;
use Twilio\Exceptions\TwilioException;

class SmsService
{
    protected ?TwilioClient $twilio = null;
    protected string $fromNumber;
    protected bool $isEnabled = false;

    public function __construct()
    {
        $this->isEnabled = QueueSetting::get('sms_enabled', true) && config('services.twilio.sid');
        
        if ($this->isEnabled && config('services.twilio.sid')) {
            try {
                $this->twilio = new TwilioClient(
                    config('services.twilio.sid'),
                    config('services.twilio.token')
                );
                $this->fromNumber = config('services.twilio.from');
            } catch (\Exception $e) {
                Log::error('Twilio initialization failed: ' . $e->getMessage());
                $this->isEnabled = false;
            }
        }
    }

    /**
     * Send SMS notification to patient
     */
    public function sendQueueNotification(Queue $queue, string $notificationType): ?NotificationLog
    {
        $patient = $queue->patient;

        if (!$patient->phone || !$patient->sms_notifications) {
            return null;
        }

        $message = $this->buildMessage($queue, $notificationType);
        
        return $this->sendSms($queue, $patient->phone, $message, $notificationType);
    }

    /**
     * Send SMS
     */
    public function sendSms(Queue $queue, string $phone, string $message, string $notificationType): NotificationLog
    {
        // Create notification log
        $log = NotificationLog::create([
            'queue_id' => $queue->id,
            'patient_id' => $queue->patient_id,
            'type' => 'sms',
            'notification_type' => $notificationType,
            'recipient' => $phone,
            'message' => $message,
            'status' => 'pending',
            'provider' => 'twilio',
        ]);

        // If SMS is not enabled or Twilio not configured, just log
        if (!$this->isEnabled || !$this->twilio) {
            Log::info("SMS (mock): To {$phone} - {$message}");
            $log->markAsSent('mock-' . uniqid());
            return $log;
        }

        try {
            $twilioMessage = $this->twilio->messages->create(
                $this->formatPhoneNumber($phone),
                [
                    'from' => $this->fromNumber,
                    'body' => $message,
                ]
            );

            $log->markAsSent($twilioMessage->sid);
            Log::info("SMS sent to {$phone}: {$twilioMessage->sid}");

        } catch (TwilioException $e) {
            $log->markAsFailed($e->getMessage());
            Log::error("SMS failed to {$phone}: " . $e->getMessage());
        }

        return $log;
    }

    /**
     * Build message based on notification type
     */
    protected function buildMessage(Queue $queue, string $notificationType): string
    {
        $patient = $queue->patient;
        $clinicName = QueueSetting::get('clinic_name', 'HealthCare Queue System');

        $replacements = [
            '{patient_name}' => $patient->first_name,
            '{queue_number}' => $queue->queue_number,
            '{position}' => $queue->position,
            '{service}' => $queue->service->name,
            '{counter}' => $queue->counter?->name ?? 'the counter',
            '{clinic_name}' => $clinicName,
            '{wait_time}' => $queue->formatted_wait_time,
        ];

        $message = match($notificationType) {
            'queue_joined' => "Hello {patient_name}! You've joined the queue at {clinic_name}. Your queue number is {queue_number}. Current position: {position}. Estimated wait: {wait_time}.",
            
            'queue_near' => QueueSetting::get('sms_near_message', 
                "Hello {patient_name}! Your queue number {queue_number} is almost up! You are position {position} in line at {clinic_name}. Please be ready."),
            
            'queue_called' => QueueSetting::get('sms_called_message',
                "Hello {patient_name}! Your queue number {queue_number} is NOW BEING CALLED! Please proceed to {counter} immediately."),
            
            'queue_completed' => "Thank you for visiting {clinic_name}! Your service ({service}) has been completed. We hope to see you again!",
            
            'queue_cancelled' => "Your queue number {queue_number} at {clinic_name} has been cancelled. Please contact us if you have any questions.",
            
            default => "Notification from {clinic_name} regarding your queue number {queue_number}.",
        };

        return str_replace(array_keys($replacements), array_values($replacements), $message);
    }

    /**
     * Format phone number for Twilio
     */
    protected function formatPhoneNumber(string $phone): string
    {
        // Remove any non-numeric characters except +
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // If it doesn't start with +, assume it needs country code
        if (!str_starts_with($phone, '+')) {
            // Default to +1 (US) - adjust as needed
            $phone = '+1' . ltrim($phone, '0');
        }

        return $phone;
    }

    /**
     * Check if SMS is enabled
     */
    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    /**
     * Get SMS statistics
     */
    public function getStats(): array
    {
        $today = today();

        return [
            'sent_today' => NotificationLog::where('type', 'sms')
                ->whereIn('status', ['sent', 'delivered'])
                ->whereDate('created_at', $today)
                ->count(),
            'failed_today' => NotificationLog::where('type', 'sms')
                ->where('status', 'failed')
                ->whereDate('created_at', $today)
                ->count(),
            'pending' => NotificationLog::where('type', 'sms')
                ->where('status', 'pending')
                ->count(),
        ];
    }
}
