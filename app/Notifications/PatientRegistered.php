<?php

namespace App\Notifications;

use App\Models\Patient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PatientRegistered extends Notification
{
    use Queueable;

    protected Patient $patient;

    /**
     * Create a new notification instance.
     */
    public function __construct(Patient $patient)
    {
        $this->patient = $patient;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to Smart Healthcare!')
            ->greeting('Hello ' . $this->patient->first_name . '!')
            ->line('Thank you for registering with Smart Healthcare.')
            ->line('Your patient account has been successfully created.')
            ->line('**Patient ID:** ' . ($this->patient->patient_id ?? $this->patient->id))
            ->line('You can now log in to your patient portal to:')
            ->line('• View your appointment history')
            ->line('• Check your queue status')
            ->line('• Manage your profile')
            ->action('Login to Patient Portal', url('/patient/login'))
            ->line('For new appointments, please visit our reception desk.')
            ->salutation('Best regards, Smart Healthcare Team');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'patient_id' => $this->patient->id,
            'message' => 'New patient registered: ' . $this->patient->full_name,
        ];
    }
}
