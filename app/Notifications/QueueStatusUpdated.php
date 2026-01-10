<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QueueStatusUpdated extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    protected $queueModel;
    protected $type;

    /**
     * Create a new notification instance.
     */
    public function __construct($queueModel, string $type)
    {
        $this->queueModel = $queueModel;
        $this->type = $type;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Default channels
        $channels = ['database'];
        
        // Return early if patient has no email
        if (!$notifiable->email) {
            return $channels;
        }

        // Check Global Email Setting
        $emailEnabled = \App\Models\QueueSetting::get('email_enabled', true);
        
        if ($emailEnabled) {
            // Check Specific Triggers
            $shouldSend = match($this->type) {
                'called' => \App\Models\QueueSetting::get('notify_on_call', true), // Default true
                'next' => \App\Models\QueueSetting::get('notify_queue_position', true), // Default true
                'completed' => \App\Models\QueueSetting::get('notify_on_complete', false), // Default false
                'joined', 'payment_required', 'payment_successful' => true, // Critical updates always send
                'serving' => true, // Usually implicit if call enabled, let's keep enabled
                'skipped', 'cancelled' => true, // Important status updates
                default => true,
            };

            if ($shouldSend) {
                $channels[] = 'mail';
            }
        }
        
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $message = $this->getMessage();
        $url = route('queue.show-ticket', $this->queueModel->queue_number);

        return (new MailMessage)
            ->subject($this->getSubject())
            ->view('emails.queue_notification', [
                'queue' => $this->queueModel,
                'messageLine' => $message,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => $this->type,
            'queue_number' => $this->queueModel->queue_number,
            'message' => $this->getMessage(),
            'title' => $this->getSubject(),
            'icon' => $this->getIcon(),
            'color' => $this->getColor(),
            'url' => route('queue.show-ticket', $this->queueModel->queue_number),
        ];
    }

    protected function getSubject(): string
    {
        return match($this->type) {
            'joined' => "[Smart Healthcare] Your Ticket {$this->queueModel->queue_number} is Ready",
            'called' => "[Smart Healthcare] It's Your Turn! Ticket {$this->queueModel->queue_number}",
            'serving' => "[Smart Healthcare] Service Started - {$this->queueModel->service->name}",
            'completed' => "[Smart Healthcare] Service Completed",
            'payment_required' => "[Smart Healthcare] Payment Required - {$this->queueModel->queue_number}",
            'payment_successful' => "[Smart Healthcare] Payment Successful",
            'next' => "[Smart Healthcare] You Are Next in Line",
            'skipped' => "[Smart Healthcare] Queue Skipped - {$this->queueModel->queue_number}",
            'cancelled' => "[Smart Healthcare] Queue Cancelled",
            default => 'Queue Update',
        };
    }

    protected function getMessage(): string
    {
        return match($this->type) {
            'joined' => "You have successfully joined the {$this->queueModel->service->name} queue. Ticket: {$this->queueModel->queue_number}",
            'called' => "Your queue number {$this->queueModel->queue_number} has been called. Proceed to {$this->queueModel->counter->name}.",
            'serving' => "Your {$this->queueModel->service->name} service has started at " . now()->format('h:i A') . ".",
            'completed' => "Your {$this->queueModel->service->name} service has been completed.",
            'payment_required' => "Payment is required for Ticket {$this->queueModel->queue_number}. Please complete payment via GCash.",
            'payment_successful' => "Payment received successfully. Thank you.",
            'next' => "You are next in line. Please be ready.",
            'skipped' => "Your queue was skipped due to no response.",
            'cancelled' => "Your queue ticket has been cancelled.",
            default => "There is an update on your queue status.",
        };
    }

    protected function getIcon(): string
    {
        return match($this->type) {
            'joined' => 'fas fa-ticket-alt',
            'called' => 'fas fa-bullhorn',
            'serving' => 'fas fa-play-circle',
            'completed' => 'fas fa-check-circle',
            'payment_required' => 'fas fa-credit-card',
            'payment_successful' => 'fas fa-receipt',
            'next' => 'fas fa-bell',
            'skipped' => 'fas fa-forward',
            'cancelled' => 'fas fa-times-circle',
            default => 'fas fa-info-circle',
        };
    }

    protected function getColor(): string
    {
        return match($this->type) {
            'joined' => 'primary',
            'called' => 'info',
            'serving' => 'warning',
            'completed' => 'success',
            'payment_required' => 'danger',
            'payment_successful' => 'success',
            'next' => 'primary',
            'skipped' => 'secondary',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }
}
