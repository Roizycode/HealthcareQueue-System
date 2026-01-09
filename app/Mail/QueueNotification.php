<?php

namespace App\Mail;

use App\Models\Queue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QueueNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $queue;
    public $type;
    public $messageLine;

    /**
     * Create a new message instance.
     */
    public function __construct(Queue $queue, string $type)
    {
        $this->queue = $queue;
        $this->type = $type;
        $this->messageLine = $this->getMessageLine($type);
    }

    private function getMessageLine($type)
    {
        switch ($type) {
            case 'queue_joined':
                return 'You have successfully joined the queue.';
            case 'queue_near':
                return 'Please be ready! You are next in line.';
            case 'queue_called':
                return 'It is your turn! Please proceed to the counter.';
            case 'queue_completed':
                return 'Your appointment has been completed. Thank you!';
            case 'queue_cancelled':
                return 'Your queue number has been cancelled.';
            default:
                return 'Here is an update on your queue status.';
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = 'Queue Update: ' . $this->queue->queue_number;
        
        if ($this->type === 'queue_called') {
            $subject = 'Action Required: Your Turn - ' . $this->queue->queue_number;
        }

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.queue_notification',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
