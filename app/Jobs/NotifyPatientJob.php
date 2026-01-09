<?php

namespace App\Jobs;

use App\Models\Queue;
use App\Models\QueueSetting;
use App\Services\SmsService;
use App\Mail\QueueNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotifyPatientJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $queueModel;
    public $type;
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(Queue $queue, string $type)
    {
        $this->queueModel = $queue;
        $this->type = $type;
    }

    /**
     * Execute the job.
     */
    public function handle(SmsService $smsService): void
    {
        try {
            // Check if notifications are enabled
            if (!$this->queueModel->patient->sms_notifications) {
                return;
            }

            // 1. Send Email Notification (Priority)
            if ($this->queueModel->patient->email) {
                try {
                    Mail::to($this->queueModel->patient->email)->send(new QueueNotification($this->queueModel, $this->type));
                    Log::info("Email sent to {$this->queueModel->patient->email} for queue {$this->queueModel->queue_number}");
                } catch (\Exception $e) {
                    Log::error("Failed to send email: " . $e->getMessage());
                }
            }

            // 2. Send SMS Notification (Secondary / Fallback to Log)
            // Even if "not API use", we can keep the logging functionality of SmsService or use it if configured
            // User requested "gmail" which implies Email.
            // We will still attempt the SmsService because it handles logic for message content generation and logging.
            // If the user wants to use a "non-API" SMS (like Android Gateway), they would implement that in SmsService.
            // For now, we assume Mail is the primary "Gmail" integration.
            
            $smsService->sendQueueNotification($this->queueModel, $this->type);

            // Update queue flags based on type
            if ($this->type === 'queue_near') {
                $this->queueModel->near_notification_sent = true;
                $this->queueModel->save();
            }

        } catch (\Exception $e) {
            Log::error("Notification job failed: " . $e->getMessage());
            // Retry logic is handled by queue worker
            throw $e;
        }
    }
}
