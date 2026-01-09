@extends('layouts.admin')

@section('title', 'Analytics - HealthQueue')

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Analytics</h4>
        <p class="text-muted small mb-0">Performance metrics and queue statistics</p>
    </div>
    <form action="{{ route('admin.analytics') }}" method="GET" class="d-flex gap-2">
        <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}">
        <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}">
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i></button>
    </form>
</div>

<!-- Charts -->
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="fw-bold mb-0">Daily Queue Volume</h6>
            </div>
            <div class="card-body">
                <canvas id="dailyVolumeChart" height="280"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="fw-bold mb-0">Service Breakdown</h6>
            </div>
            <div class="card-body">
                <canvas id="serviceBreakdownChart" height="280"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="fw-bold mb-0">Average Wait Time (Minutes)</h6>
            </div>
            <div class="card-body">
                <canvas id="waitTimeChart" height="220"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="fw-bold mb-0">Hourly Traffic (Today)</h6>
            </div>
            <div class="card-body">
                <canvas id="hourlyTrafficChart" height="220"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const primaryColor = '#0D6EFD';
    const secondaryColor = '#20C997';
    const accentColor = '#0dcaf0';
    const warningColor = '#FFC107';
    
    // Daily Volume Chart
    new Chart(document.getElementById('dailyVolumeChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode($dailyStats->pluck('date')) !!},
            datasets: [{
                label: 'Total',
                data: {!! json_encode($dailyStats->pluck('total')) !!},
                borderColor: primaryColor,
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Completed',
                data: {!! json_encode($dailyStats->pluck('completed')) !!},
                borderColor: secondaryColor,
                backgroundColor: 'rgba(32, 201, 151, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'top' } }, scales: { y: { beginAtZero: true } } }
    });

    // Service Breakdown Chart
    new Chart(document.getElementById('serviceBreakdownChart'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($serviceBreakdown->map(fn($s) => $s->service->name ?? 'Unknown')) !!},
            datasets: [{
                data: {!! json_encode($serviceBreakdown->pluck('total')) !!},
                backgroundColor: {!! json_encode($serviceBreakdown->map(fn($s) => $s->service->color ?? '#6c757d')) !!},
                borderWidth: 0
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } }, cutout: '70%' }
    });

    // Wait Time Chart
    new Chart(document.getElementById('waitTimeChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($dailyStats->pluck('date')) !!},
            datasets: [{
                label: 'Avg Wait (min)',
                data: {!! json_encode($dailyStats->map(fn($s) => round($s->avg_wait_time ?? 0, 1))) !!},
                backgroundColor: accentColor,
                borderRadius: 4
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });

    // Hourly Traffic Chart
    new Chart(document.getElementById('hourlyTrafficChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_map(fn($h) => sprintf('%02d:00', $h), array_keys($hourlyData))) !!},
            datasets: [{
                label: 'Patients',
                data: {!! json_encode(array_values($hourlyData)) !!},
                backgroundColor: warningColor,
                borderRadius: 4
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });
</script>
@endpush
