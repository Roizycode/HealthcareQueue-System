@extends('layouts.admin')

@section('title', 'Reports - HealthQueue')

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Reports</h4>
        <p class="text-muted small mb-0">Generate and view system reports</p>
    </div>
    <div class="btn-group btn-group-sm">
        <button class="btn btn-outline-primary" onclick="generateReport('daily')">
            <i class="fas fa-calendar-day me-1"></i> Daily
        </button>
        <button class="btn btn-outline-primary" onclick="generateReport('weekly')">
            <i class="fas fa-calendar-week me-1"></i> Weekly
        </button>
        <button class="btn btn-outline-primary" onclick="generateReport('monthly')">
            <i class="fas fa-calendar-alt me-1"></i> Monthly
        </button>
    </div>
</div>

<!-- Summary Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <small class="text-muted text-uppercase d-block mb-1" style="font-size: 0.65rem;">Total Patients Today</small>
                <h4 class="fw-bold text-primary mb-0">{{ $stats['patients_today'] ?? 0 }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <small class="text-muted text-uppercase d-block mb-1" style="font-size: 0.65rem;">Completed Services</small>
                <h4 class="fw-bold text-success mb-0">{{ $stats['completed_today'] ?? 0 }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <small class="text-muted text-uppercase d-block mb-1" style="font-size: 0.65rem;">Avg Wait Time</small>
                <h4 class="fw-bold text-warning mb-0">{{ $stats['avg_wait_time'] ?? 0 }} min</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <small class="text-muted text-uppercase d-block mb-1" style="font-size: 0.65rem;">Avg Service Time</small>
                <h4 class="fw-bold text-info mb-0">{{ $stats['avg_service_time'] ?? 0 }} min</h4>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Service Performance -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="fw-bold mb-0"><i class="fas fa-chart-pie text-primary me-2"></i>Service Performance</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover text-center" style="font-size: 0.8rem;">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-bottom-0">Service</th>
                                <th class="border-bottom-0">Patients</th>
                                <th class="border-bottom-0">Completed</th>
                                <th class="border-bottom-0">Avg Wait</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($serviceStats ?? [] as $stat)
                            <tr>
                                <td>
                                    <span class="badge" style="background: {{ $stat->service->color ?? '#6c757d' }}">{{ $stat->service->name ?? 'N/A' }}</span>
                                </td>
                                <td>{{ $stat->total }}</td>
                                <td>{{ $stat->completed }}</td>
                                <td>{{ round($stat->avg_wait_time ?? 0) }} min</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-muted py-4">No service data available</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Staff Performance -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="fw-bold mb-0"><i class="fas fa-user-md text-success me-2"></i>Staff Performance</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover text-center" style="font-size: 0.8rem;">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-bottom-0">Staff</th>
                                <th class="border-bottom-0">Served</th>
                                <th class="border-bottom-0">Avg Service</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($staffStats ?? [] as $stat)
                            <tr>
                                <td class="text-start">{{ $stat->staff->name ?? 'N/A' }}</td>
                                <td>{{ $stat->total_served }}</td>
                                <td>{{ round($stat->avg_service_time ?? 0) }} min</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-muted py-4">No staff data available</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Daily Queue Chart -->
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="fw-bold mb-0"><i class="fas fa-chart-line text-info me-2"></i>Daily Queue Volume (Last 7 Days)</h6>
            </div>
            <div class="card-body">
                <canvas id="queueChart" height="100"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const ctx = document.getElementById('queueChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($chartLabels ?? ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']) !!},
        datasets: [{
            label: 'Patients',
            data: {!! json_encode($chartData ?? [0, 0, 0, 0, 0, 0, 0]) !!},
            backgroundColor: 'rgba(13, 110, 253, 0.7)',
            borderRadius: 4
        }]
    },
    options: {
        responsive: true,
        scales: { y: { beginAtZero: true } }
    }
});

function generateReport(type) {
    Swal.fire({
        title: 'Generate ' + type.charAt(0).toUpperCase() + type.slice(1) + ' Report',
        text: 'This will generate a PDF report',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-download me-1"></i> Generate'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({ icon: 'success', title: 'Report Generated!', text: 'Download will start shortly', timer: 2000, showConfirmButton: false });
        }
    });
}
</script>
@endpush
