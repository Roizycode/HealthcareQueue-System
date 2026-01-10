<?php

namespace App\Notifications;

use App\Models\AppointmentRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentRequestStatus extends Notification
{
    use Queueable;

    protected AppointmentRequest $request;
    protected string $type;

    /**
     * Create a new notification instance.
     * @param string $type - 'submitted', 'approved', 'rejected'
     */
    public function __construct(AppointmentRequest $request, string $type = 'submitted')
    {
        $this->request = $request;
        $this->type = $type;
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
        $mail = new MailMessage;

        if ($this->type === 'submitted') {
            $mail->subject('Appointment Request Received - Smart Healthcare')
                ->greeting('Hello ' . $this->request->patient->first_name . '!')
                ->line('Your appointment request has been received and is pending approval.')
                ->line('**Request Details:**')
                ->line('• Service: ' . $this->request->service->name)
                ->line('• Preferred Date: ' . $this->request->preferred_date->format('F d, Y'))
                ->line('• Preferred Time: ' . $this->request->formatted_time)
                ->line('You will receive an email notification once your request has been reviewed.')
                ->action('View My Appointments', url('/patient/appointments'))
                ->salutation('Best regards, Smart Healthcare Team');
        } elseif ($this->type === 'approved') {
            $mail->subject('✅ Appointment Request Approved - Smart Healthcare')
                ->greeting('Great news, ' . $this->request->patient->first_name . '!')
                ->line('Your appointment request has been **approved**.')
                ->line('**Appointment Details:**')
                ->line('• Service: ' . $this->request->service->name)
                ->line('• Date: ' . $this->request->preferred_date->format('F d, Y'))
                ->line('• Time: ' . $this->request->formatted_time);
            
            if ($this->request->staff_notes) {
                $mail->line('**Staff Note:** ' . $this->request->staff_notes);
            }
            
            $mail->line('Please arrive 15 minutes before your scheduled time.')
                ->action('View My Appointments', url('/patient/appointments'))
                ->salutation('We look forward to seeing you!');
        } elseif ($this->type === 'rejected') {
            $mail->subject('Appointment Request Update - Smart Healthcare')
                ->greeting('Hello ' . $this->request->patient->first_name . ',')
                ->line('Unfortunately, your appointment request could not be approved at this time.')
                ->line('**Request Details:**')
                ->line('• Service: ' . $this->request->service->name)
                ->line('• Preferred Date: ' . $this->request->preferred_date->format('F d, Y'));
            
            if ($this->request->staff_notes) {
                $mail->line('**Reason:** ' . $this->request->staff_notes);
            }
            
            $mail->line('Please submit a new request with different date/time, or contact our reception for assistance.')
                ->action('Submit New Request', url('/patient/dashboard'))
                ->salutation('Best regards, Smart Healthcare Team');
        }

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'request_id' => $this->request->id,
            'type' => $this->type,
            'service' => $this->request->service->name,
        ];
    }
}
