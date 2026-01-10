<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f8f9fa; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { text-align: center; border-bottom: 2px solid #0D6EFD; padding-bottom: 20px; margin-bottom: 20px; }
        .header h1 { color: #0D6EFD; margin: 0; font-size: 24px; }
        .content { padding: 10px 0; }
        .queue-number { font-size: 32px; font-weight: bold; color: #0D6EFD; text-align: center; margin: 20px 0; background: #e7f1ff; padding: 10px; border-radius: 8px; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 8px; border-bottom: 1px solid #eee; }
        .label { font-weight: bold; color: #666; width: 40%; }
        .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #999; border-top: 1px solid #eee; padding-top: 10px; }
        .btn { display: inline-block; padding: 10px 20px; background: #0D6EFD; color: #fff; text-decoration: none; border-radius: 5px; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Optional Header Logo or Title if desired, keeping minimal as per text example -->
        
        <div class="content">
            <p>Hello {{ $queue->patient->first_name }},</p>
            
            <p>You have successfully joined the {{ $queue->service->name }} queue.</p>
            
            <div style="margin: 20px 0;">
                <div><strong>Ticket:</strong> {{ $queue->queue_number }}</div>
                @if($queue->counter)
                <div><strong>Counter:</strong> {{ $queue->counter->name }}</div>
                @endif
                @if($queue->status === 'waiting')
                <div><strong>Current Position:</strong> {{ $queue->position }}</div>
                <div><strong>Estimated Wait:</strong> {{ $queue->formatted_wait_time ?? '15 mins' }}</div>
                @endif
            </div>
            
            <div style="margin: 30px 0;">
                <a href="{{ route('queue.show-ticket', ['queue' => $queue->queue_number]) }}" 
                   style="background-color:#F01E2C;color:#fff;padding:10px 20px;text-decoration:none;border-radius:5px;display:inline-block;font-weight:bold;">
                   View Ticket
                </a>
            </div>
            
            <p>Thank you for using Smart Healthcare!</p>
            
            <p><strong>Smart Healthcare Team</strong></p>
            
            <div style="margin-top: 30px; font-size: 12px; color: #666; border-top: 1px solid #eee; padding-top: 15px;">
                Need help? Contact us at <a href="mailto:info@Smart Healthcare.com" style="color: #F01E2C; text-decoration: none;">info@Smart Healthcare.com</a> or call +1 (234) 567-8900
            </div>
        </div>
    </div>
</body>
</html>
