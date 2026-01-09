@extends('layouts.admin')

@section('title', 'Admin Dashboard - HealthQueue')

@section('content')
<div class="row g-4">
    <!-- LEFT COLUMN: Overview Stats -->
    <div class="col-lg-5 col-xl-4">
        <!-- Header / Status -->
        <div class="mb-4">
            <h4 class="fw-bold text-dark">Dashboard</h4>
            <p class="text-muted small mb-0">
                Welcome, {{ auth()->user()->name ?? 'Admin' }} &bull; {{ now()->format('l, F j') }}
            </p>
        </div>

        <!-- Today's Overview Card -->
        <div class="card border-0 shadow-sm mb-4 bg-primary text-white overflow-hidden">
            <div class="card-body p-4 text-center position-relative">
                <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, transparent 100%); pointer-events: none;"></div>
                
                <p class="text-white-50 small text-uppercase mb-2">Today's Patients</p>
                <h1 class="display-3 fw-bold mb-0">{{ $todaySummary['total_patients'] }}</h1>
                <p class="text-white-50 small mt-1 mb-3">Total registered today</p>
                
                <div class="d-grid gap-2">
                    <a href="{{ route('staff.dashboard') }}" class="btn btn-light fw-bold">
                        <i class="fas fa-desktop me-2"></i>Open Staff View
                    </a>
                    <div class="row g-2">
                        <div class="col-6">
                            <a href="{{ route('display') }}" target="_blank" class="btn btn-outline-light btn-sm w-100">
                                <i class="fas fa-tv me-1"></i> Display
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('admin.analytics') }}" class="btn btn-outline-light btn-sm w-100">
                                <i class="fas fa-chart-pie me-1"></i> Reports
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-0">
                <div class="row g-0 text-center">
                    <div class="col-3 p-3 border-end">
                        <small class="text-muted text-uppercase d-block mb-1" style="font-size: 0.65rem;">Waiting</small>
                        <h5 class="fw-bold text-warning mb-0">{{ $todaySummary['waiting'] }}</h5>
                    </div>
                    <div class="col-3 p-3 border-end">
                        <small class="text-muted text-uppercase d-block mb-1" style="font-size: 0.65rem;">Served</small>
                        <h5 class="fw-bold text-success mb-0">{{ $todaySummary['completed'] }}</h5>
                    </div>
                    <div class="col-3 p-3 border-end">
                        <small class="text-muted text-uppercase d-block mb-1" style="font-size: 0.65rem;">Serving</small>
                        <h5 class="fw-bold text-primary mb-0">{{ $todaySummary['serving'] }}</h5>
                    </div>
                    <div class="col-3 p-3">
                        <small class="text-muted text-uppercase d-block mb-1" style="font-size: 0.65rem;">Skipped</small>
                        <h5 class="fw-bold text-danger mb-0">{{ $todaySummary['skipped'] }}</h5>
                    </div>
                </div>
            </div>
        </div>

        <!-- Avg Wait Time -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <small class="text-muted text-uppercase" style="font-size: 0.7rem;">Average Wait Time</small>
                    <h5 class="fw-bold mb-0">{{ $todaySummary['average_wait_time'] }} <small class="text-muted fw-normal">min</small></h5>
                </div>
                <div class="rounded-circle bg-info bg-opacity-10 p-2">
                    <i class="fas fa-stopwatch text-info"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- RIGHT COLUMN: Service Queues & Counters -->
    <div class="col-lg-7 col-xl-8">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0">Service Queues</h5>
            <a href="{{ route('admin.settings') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-cog me-1"></i> Settings
            </a>
        </div>

        <!-- Service Queue Table -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-center" style="font-size: 0.8rem;">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-bottom-0">Service</th>
                                <th class="border-bottom-0">Waiting</th>
                                <th class="border-bottom-0">Done</th>
                                <th class="border-bottom-0">Avg</th>
                                <th class="border-bottom-0">Now Serving</th>
                                <th class="border-bottom-0">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($serviceStats as $stat)
                            <tr>
                                <td>
                                    <div class="d-inline-flex align-items-center">
                                        <span class="rounded-circle d-inline-block me-2" style="width: 8px; height: 8px; background: {{ $stat['color'] }};"></span>
                                        <div class="text-start">
                                            <span class="fw-bold">{{ $stat['name'] }}</span>
                                            <small class="text-muted d-block" style="font-size: 0.7rem;">{{ $stat['code'] }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-warning text-dark">{{ $stat['waiting'] }}</span>
                                </td>
                                <td class="text-success fw-bold">{{ $stat['completed_today'] }}</td>
                                <td class="text-muted">{{ $stat['average_wait_time'] }}m</td>
                                <td>
                                    @if($stat['current_serving'])
                                        <span class="badge bg-primary">{{ $stat['current_serving'] }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.service.queue', ['service' => $stat['id']]) }}" class="btn btn-sm btn-outline-primary">
                                        Manage
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Counter Status Table -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0"><i class="fas fa-desktop text-primary me-2"></i>Counter Status</h6>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-center" style="font-size: 0.8rem;">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-bottom-0">Counter</th>
                                <th class="border-bottom-0">Service</th>
                                <th class="border-bottom-0">Staff</th>
                                <th class="border-bottom-0">Status</th>
                                <th class="border-bottom-0">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($counters as $counter)
                                <tr>
                                    <td class="fw-bold">{{ $counter->name }}</td>
                                    <td>
                                        <span class="d-inline-block rounded-circle me-1" style="width: 6px; height: 6px; background: {{ $counter->service->color ?? '#6c757d' }};"></span>
                                        {{ $counter->service->name ?? 'N/A' }}
                                    </td>
                                    <td class="text-muted">{{ $counter->assignedStaff->name ?? 'Unassigned' }}</td>
                                    <td>
                                        @if($counter->status === 'open')
                                            <span class="badge bg-success">Open</span>
                                        @elseif($counter->status === 'break')
                                            <span class="badge bg-warning text-dark">Break</span>
                                        @else
                                            <span class="badge bg-secondary">Closed</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-success" 
                                                    onclick="openCounter({{ $counter->id }}, '{{ $counter->name }}')"
                                                    {{ $counter->status === 'open' ? 'disabled' : '' }}
                                                    title="Open">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-outline-danger"
                                                    onclick="closeCounter({{ $counter->id }}, '{{ $counter->name }}')"
                                                    {{ $counter->status === 'closed' ? 'disabled' : '' }}
                                                    title="Close">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="fas fa-desktop fa-2x mb-2 opacity-25"></i>
                                        <p class="mb-0 small">No counters configured</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openCounter(counterId, counterName) {
    Swal.fire({
        title: 'Open Counter?',
        text: `Are you sure you want to open ${counterName}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-check me-1"></i> Yes, Open',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            toggleCounter(counterId, 'open');
        }
    });
}

function closeCounter(counterId, counterName) {
    Swal.fire({
        title: 'Close Counter?',
        text: `Are you sure you want to close ${counterName}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-times me-1"></i> Yes, Close',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            toggleCounter(counterId, 'closed');
        }
    });
}

function toggleCounter(counterId, status) {
    fetch(`/admin/counters/${counterId}/status/${status}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: status === 'open' ? 'Counter Opened!' : 'Counter Closed!',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Failed to update counter'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to update counter'
        });
    });
}

// Auto-refresh every 30 seconds
setTimeout(function() {
    location.reload();
}, 30000);
</script>
@endpush
