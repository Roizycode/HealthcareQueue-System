<!DOCTYPE html>
<html>
<head>
    <title>Receipt - {{ $queue->queue_number }}</title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; color: #000; margin: 0; padding: 20px; font-size: 14px; }
        .receipt { width: 320px; margin: 0 auto; background: #fff; }
        .header { text-align: center; margin-bottom: 15px; border-bottom: 1px dashed #000; padding-bottom: 10px; }
        .header h3 { margin: 0 0 5px 0; }
        .header p { margin: 0; font-size: 12px; }
        .details { margin-bottom: 15px; }
        .row { display: flex; justify-content: space-between; margin-bottom: 5px; }
        .total { border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 10px 0; font-weight: bold; margin-bottom: 15px; }
        .footer { text-align: center; font-size: 12px; margin-top: 20px; }
        @media print {
            body { padding: 0; margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="receipt">
        <div class="header">
            <h3>Smart Healthcare</h3>
            <p>123 Medical Center Drive<br>Metro Manila, Philippines</p>
            <p style="margin-top: 10px; font-weight: bold;">OFFICIAL RECEIPT</p>
        </div>
        
        <div class="details">
            <div class="row">
                <span>Date:</span>
                <span>{{ now()->format('M d, Y H:i') }}</span>
            </div>
            <div class="row">
                <span>Receipt No:</span>
                <span>{{ str_pad($queue->id, 8, '0', STR_PAD_LEFT) }}</span>
            </div>
            <div class="row">
                <span>Queue No:</span>
                <span>{{ $queue->queue_number }}</span>
            </div>
            <div class="row">
                <span>Patient:</span>
                <span>{{ Str::limit($queue->patient->full_name, 20) }}</span>
            </div>
        </div>

        <div style="margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 5px;">
            <div class="row" style="font-weight: bold;">
                <span>Description</span>
                <span>Amount</span>
            </div>
        </div>
        
        <div class="details">
            <div class="row">
                <span>{{ $queue->service->name }}</span>
                <span>500.00</span>
            </div>
        </div>

        <div class="total">
            <div class="row">
                <span>TOTAL AMOUNT</span>
                <span>P 500.00</span>
            </div>
        </div>
        
        <div class="details">
            <div class="row">
                <span>Payment Method</span>
                <span>CASH</span>
            </div>
            <div class="row">
                <span>Status</span>
                <span>PAID</span>
            </div>
        </div>

        <div class="footer">
            <p>Thank you for choosing Smart Healthcare!</p>
            <p>Please keep this receipt for your records.</p>
        </div>
        
        <button class="no-print" onclick="window.print()" style="width: 100%; padding: 12px; margin-top: 20px; background: #000; color: #fff; border: none; cursor: pointer;">Print Receipt</button>
        <button class="no-print" onclick="window.close()" style="width: 100%; padding: 12px; margin-top: 10px; background: #eee; color: #000; border: none; cursor: pointer;">Close Window</button>
    </div>
</body>
</html>
