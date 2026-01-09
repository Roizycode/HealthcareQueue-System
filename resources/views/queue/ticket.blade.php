<!DOCTYPE html>
<html>
<head>
    <title>Ticket - {{ $queue->queue_number }}</title>
    <link rel="icon" type="image/png" href="{{ asset('image/Iconlogo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f3f4f6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .ticket-wrapper {
            width: 300px; /* Standard thermal width approx */
            position: relative;
        }

        .ticket {
            background: #fff;
            padding: 25px 20px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .header {
            margin-bottom: 15px;
            border-bottom: 2px dashed #000;
            padding-bottom: 15px;
        }

        .brand {
            font-size: 1.1rem; /* Smaller to fit single line if possible, or wrap nicely */
            font-weight: 900;
            color: #000;
            text-transform: uppercase;
            line-height: 1.2;
            margin-bottom: 5px;
        }

        .timestamp {
            font-size: 0.7rem;
            color: #555;
            font-family: monospace;
        }

        .content {
            padding: 10px 0;
        }

        .label {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #666;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .queue-number {
            font-size: 3.5rem;
            font-weight: 900;
            color: #000;
            line-height: 1;
            margin-bottom: 15px;
            font-variant-numeric: tabular-nums;
        }

        .service-name {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 5px;
            color: #000;
        }

        .patient-name {
            font-size: 0.9rem;
            color: #333;
        }

        .details-box {
            border: 1px solid #000;
            padding: 8px;
            margin: 15px 0;
            font-size: 0.8rem;
        }
        
        .footer {
            margin-top: 20px;
            border-top: 2px dashed #000;
            padding-top: 15px;
            font-size: 0.7rem;
            color: #555;
        }

        .actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            flex-direction: column;
        }

        .btn {
            display: block;
            width: 100%;
            padding: 12px;
            border: none;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            text-transform: uppercase;
            transition: opacity 0.2s;
        }

        .btn-print { background: #000; color: #fff; }
        .btn-close { background: #e5e7eb; color: #374151; }
        .btn:hover { opacity: 0.9; }

        @media print {
            body { 
                background: #fff; 
                padding: 0; 
                margin: 0; 
                display: block; 
                height: auto;
                min-height: 0;
            }
            .ticket-wrapper { 
                width: 100%; 
                margin: 0; /* Remove auto margin */
                position: static;
            }
            .ticket { 
                box-shadow: none; 
                padding: 10px 0; /* Less padding for print */
                width: 100%;
                max-width: 80mm; /* Force thermal width */
            }
            .actions { display: none !important; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="ticket-wrapper">
        <div class="ticket">
            <div class="header">
                <div class="brand">HealthCareQueueSystem</div>
                <div class="timestamp">{{ now()->format('M d, Y • h:i A') }}</div>
            </div>
            
            <div class="content">
                <div class="label">Your Ticket Number</div>
                <div class="queue-number">{{ $queue->queue_number }}</div>
                
                <div class="service-name">{{ $queue->service->name }}</div>
                <div class="patient-name">{{ $queue->patient->full_name }}</div>

                @if($queue->position > 0)
                <div class="details-box">
                    <div style="font-weight: bold;">{{ $queue->position }} People Ahead</div>
                    <div>Est. Wait: {{ $queue->formatted_wait_time ?? '~ mins' }}</div>
                </div>
                @endif
            </div>

            <div class="footer">
                <p style="margin: 0 0 5px 0;">Please wait for your number to be called.</p>
                <div class="label" style="margin-top: 10px;">Check Status Online</div>
                <div style="font-weight: bold; font-size: 0.8rem;">
                    {{ request()->getHost() }}/check-status
                </div>
            </div>
        </div>

        <div class="actions">
            <button class="btn btn-print" onclick="window.print()">Print Ticket</button>
            <button class="btn btn-close" onclick="window.close()">Close Window</button>
        </div>
    </div>
</body>
</html>
