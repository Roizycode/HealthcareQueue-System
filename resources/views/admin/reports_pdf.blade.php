<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ ucfirst($type) }} Report - Smart Healthcare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #0D6EFD;
            padding-bottom: 20px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #0D6EFD;
        }
        .report-title {
            font-size: 18px;
            font-weight: bold;
            margin-top: 10px;
            text-transform: uppercase;
        }
        .meta-info {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            background-color: #f8f9fa;
            padding: 8px 12px;
            border-left: 4px solid #0D6EFD;
            margin: 20px 0 10px 0;
            page-break-after: avoid;
        }
        table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
            page-break-inside: auto;
        }
        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        th, td {
            padding: 8px;
            border: 1px solid #dee2e6;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11px;
        }
        .badge-custom {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            color: #fff;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white;
            }
        }
    </style>
</head>
<body onload="window.print()">

    <!-- Print Controls -->
    <div class="container mt-3 mb-4 no-print text-center">
        <button onclick="window.print()" class="btn btn-primary btn-sm"><i class="fas fa-print me-1"></i> Print / Save as PDF</button>
        <button onclick="window.close()" class="btn btn-secondary btn-sm ms-2">Close</button>
    </div>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="logo">Smart Healthcare System</div>
            <div class="report-title">{{ ucfirst($type) }} Queue Report</div>
            <div class="meta-info">
                Generated on: {{ now()->format('F j, Y h:i A') }} <br>
                Period: {{ $period }}
            </div>
        </div>

        <!-- Summary Stats -->
        <div class="row mb-4">
            <div class="col-3 text-center">
                <div class="border rounded p-2">
                    <div class="text-muted small">Total Patients</div>
                    <div class="fw-bold fs-5">{{ $summary['total'] }}</div>
                </div>
            </div>
            <div class="col-3 text-center">
                <div class="border rounded p-2">
                    <div class="text-muted small">Completed</div>
                    <div class="fw-bold fs-5 text-success">{{ $summary['completed'] }}</div>
                </div>
            </div>
            <div class="col-3 text-center">
                <div class="border rounded p-2">
                    <div class="text-muted small">Cancelled/Skipped</div>
                    <div class="fw-bold fs-5 text-danger">{{ $summary['cancelled'] }}</div>
                </div>
            </div>
            <div class="col-3 text-center">
                <div class="border rounded p-2">
                    <div class="text-muted small">Avg Wait Time</div>
                    <div class="fw-bold fs-5 text-primary">{{ $summary['avg_wait'] }} min</div>
                </div>
            </div>
        </div>

        <!-- Service Performance -->
        <div class="section-title">Service Breakdown</div>
        <table>
            <thead>
                <tr>
                    <th>Service</th>
                    <th class="text-center">Total Tickets</th>
                    <th class="text-center">Completed</th>
                    <th class="text-center">Avg Service Time</th>
                </tr>
            </thead>
            <tbody>
                @foreach($serviceStats as $stat)
                <tr>
                    <td>{{ $stat->service->name }}</td>
                    <td class="text-center">{{ $stat->total }}</td>
                    <td class="text-center">{{ $stat->completed }}</td>
                    <td class="text-center">{{ round($stat->avg_service_time ?? 0) }} min</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Staff Performance -->
        <div class="section-title">Staff Performance</div>
        <table>
            <thead>
                <tr>
                    <th>Staff Member</th>
                    <th class="text-center">Patients Served</th>
                    <th class="text-center">Avg Handle Time</th>
                </tr>
            </thead>
            <tbody>
                @foreach($staffStats as $stat)
                <tr>
                    <td>{{ $stat->staff->name }}</td>
                    <td class="text-center">{{ $stat->total_served }}</td>
                    <td class="text-center">{{ round($stat->avg_service_time ?? 0) }} min</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Detailed List (First 100) -->
        <div class="section-title">Detailed Logs (Top 100)</div>
        <table>
            <thead>
                <tr>
                    <th>Ticket</th>
                    <th>Patient</th>
                    <th>Service</th>
                    <th>Status</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                </tr>
            </thead>
            <tbody>
                @foreach($details as $queue)
                <tr>
                    <td><strong>{{ $queue->queue_number }}</strong></td>
                    <td>{{ $queue->patient->first_name }} {{ $queue->patient->last_name }}</td>
                    <td>{{ $queue->service->name }}</td>
                    <td>
                        <span class="badge-custom" style="
                            background-color: 
                            @if($queue->status == 'completed') #198754 
                            @elseif($queue->status == 'cancelled') #dc3545 
                            @elseif($queue->status == 'skipped') #ffc107 
                            @else #6c757d @endif;
                            color: @if($queue->status == 'skipped') #000 @else #fff @endif;">
                            {{ ucfirst($queue->status) }}
                        </span>
                    </td>
                    <td>{{ $queue->created_at->format('H:i') }}</td>
                    <td>{{ $queue->updated_at->format('H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer">
            This report is system-generated by Smart Healthcare Queue System on {{ now()->format('Y-m-d H:i:s') }}
        </div>
    </div>
</body>
</html>
