@extends('layouts.patient')

@section('title', 'Dashboard - Smart Healthcare')

@section('content')
<!-- Page Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Welcome back, {{ $user->name }}!</h4>
        <p class="text-muted mb-0">
            <i class="fas fa-calendar-day me-1"></i>
            {{ now()->format('l, F j, Y') }}
        </p>
    </div>
    @if($patient)
        <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 mt-2 mt-md-0">
            <i class="fas fa-id-card me-1"></i> Patient ID: {{ $patient->patient_id ?? $patient->id }}
        </span>
    @endif
</div>

<!-- Active Queue Section -->
<div id="active-queue-wrapper">
@if($activeQueue)
<div class="patient-card mb-4 p-0 overflow-hidden" id="activeQueueCard">
    <div class="p-4" style="background: linear-gradient(135deg, #20C997 0%, #0dcaf0 100%);">
        <div class="row align-items-center text-white">
            <div class="col-md-6">
                <span class="badge bg-white bg-opacity-25 text-white mb-2">
                    <i class="fas fa-clock me-1"></i> Active Queue
                </span>
                <div class="display-4 fw-bold mb-2" id="queueNumber">{{ $activeQueue->queue_number }}</div>
                <p class="mb-0 opacity-75">
                    <i class="fas fa-stethoscope me-1"></i> <span id="serviceName">{{ $activeQueue->service->name }}</span>
                </p>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0" id="queueStatusContainer">
                @if($activeQueue->status === 'waiting')
                    <span class="badge bg-warning text-dark px-3 py-2 fs-6">
                        <i class="fas fa-hourglass-half me-1"></i> Waiting
                    </span>
                    <p class="mb-0 opacity-75 small mt-2">Please wait for your number to be called</p>
                @elseif($activeQueue->status === 'called')
                    <span class="badge bg-success text-white px-3 py-2 fs-6 animate-pulse">
                        <i class="fas fa-bullhorn me-1"></i> YOUR TURN!
                    </span>
                    <p class="mb-0 mt-2">
                        Please proceed to <strong>{{ $activeQueue->counter?->name ?? 'Counter' }}</strong>
                    </p>
                @elseif($activeQueue->status === 'serving')
                    <span class="badge bg-info text-white px-3 py-2 fs-6">
                        <i class="fas fa-user-md me-1"></i> Being Served
                    </span>
                    <p class="mb-0 opacity-75 mt-2">
                        At <strong>{{ $activeQueue->counter?->name ?? 'Counter' }}</strong>
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>
@else
<!-- No Active Queue -->
<div class="patient-card p-4 text-center mb-4">
    <div class="py-3">
        <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
            <i class="fas fa-ticket-alt text-muted fa-2x"></i>
        </div>
        <h5 class="fw-bold mb-2">No Active Queue</h5>
        <p class="text-muted small mb-3">
            You don't have a queue ticket for today yet.
        </p>
        <a href="{{ route('patient.request-appointment') }}" class="btn btn-primary">
            <i class="fas fa-calendar-plus me-1"></i> Request Appointment
        </a>
    </div>
</div>
@endif
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="patient-card p-3 text-center">
            <div class="rounded-3 d-inline-flex align-items-center justify-content-center mb-2" style="width: 45px; height: 45px; background: rgba(32, 201, 151, 0.1);">
                <i class="fas fa-calendar-check text-success"></i>
            </div>
            <div class="fs-3 fw-bold text-success" id="stats-total">{{ $stats['total_visits'] }}</div>
            <div class="text-muted small">Total Visits</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="patient-card p-3 text-center">
            <div class="rounded-3 d-inline-flex align-items-center justify-content-center mb-2" style="width: 45px; height: 45px; background: rgba(25, 135, 84, 0.1);">
                <i class="fas fa-check-circle text-success"></i>
            </div>
            <div class="fs-3 fw-bold text-success" id="stats-completed">{{ $stats['completed'] }}</div>
            <div class="text-muted small">Completed</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="patient-card p-3 text-center">
            <div class="rounded-3 d-inline-flex align-items-center justify-content-center mb-2" style="width: 45px; height: 45px; background: rgba(255, 193, 7, 0.1);">
                <i class="fas fa-clock text-warning"></i>
            </div>
            <div class="fs-3 fw-bold text-warning" id="stats-pending">{{ $stats['pending'] }}</div>
            <div class="text-muted small">Pending</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="patient-card p-3 text-center">
            <div class="rounded-3 d-inline-flex align-items-center justify-content-center mb-2" style="width: 45px; height: 45px; background: rgba(220, 53, 69, 0.1);">
                <i class="fas fa-times-circle text-danger"></i>
            </div>
            <div class="fs-3 fw-bold text-danger" id="stats-cancelled">{{ $stats['cancelled'] }}</div>
            <div class="text-muted small">Cancelled</div>
        </div>
    </div>
</div>

<!-- Recent Appointments -->
<div class="row">
    <div class="col-lg-8">
        <div class="patient-card">
            <div class="p-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">
                        <i class="fas fa-history text-success me-2"></i>Recent Appointments
                    </h6>
                    <a href="{{ route('patient.appointments') }}" class="btn btn-sm btn-outline-success rounded-pill">
                        View All <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
            <div class="p-0" id="recent-appointments-container">
                @if($queueHistory->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0 ps-3">Queue #</th>
                                    <th class="border-0">Service</th>
                                    <th class="border-0">Date</th>
                                    <th class="border-0 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($queueHistory->take(5) as $queue)
                                <tr>
                                    <td class="ps-3">
                                        <span class="fw-bold text-success">{{ $queue->queue_number }}</span>
                                    </td>
                                    <td>{{ $queue->service->name }}</td>
                                    <td class="text-muted small">{{ $queue->created_at->format('M d, Y') }}</td>
                                    <td class="text-center">
                                        @php
                                            $statusClass = match($queue->status) {
                                                'completed' => 'success',
                                                'cancelled' => 'danger',
                                                'skipped' => 'warning',
                                                'waiting', 'called', 'serving' => 'info',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $statusClass }} bg-opacity-10 text-{{ $statusClass }}">
                                            {{ ucfirst($queue->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-calendar-times fa-2x mb-3"></i>
                        <p class="mb-2">No appointments yet</p>
                        <a href="{{ route('patient.request-appointment') }}" class="btn btn-sm btn-outline-success">
                            Schedule Now
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="col-lg-4 mt-4 mt-lg-0">
        <div class="patient-card p-4">
            <h6 class="fw-bold mb-3">
                <i class="fas fa-bolt text-warning me-2"></i>Quick Actions
            </h6>
            
            <a href="{{ route('patient.request-appointment') }}" class="btn btn-success w-100 mb-2">
                <i class="fas fa-calendar-plus me-2"></i> Request Appointment
            </a>
            <a href="{{ route('patient.my-requests') }}" class="btn btn-outline-success w-100 mb-2">
                <i class="fas fa-clipboard-list me-2"></i> My Requests
            </a>
            <a href="{{ route('patient.queue-check') }}" class="btn btn-outline-primary w-100 mb-2">
                <i class="fas fa-search me-2"></i> Check Queue Status
            </a>
            <a href="{{ route('patient.live-display') }}" class="btn btn-outline-secondary w-100">
                <i class="fas fa-tv me-2"></i> Live Display
            </a>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }
    
    .animate-pulse {
        animation: pulse 1.5s ease-in-out infinite;
    }
</style>
@endpush

@push('scripts')
<script>
    let lastStatus = null;
    let initialLoad = true;

    function updateDashboard() {
        fetch('{{ route("patient.api.dashboard-stats") }}')
            .then(r => r.json())
            .then(data => {
                if (!data.success) return;

                // 1. Update Stats
                const stats = data.stats;
                if(stats) {
                    if(document.getElementById('stats-total')) document.getElementById('stats-total').innerText = stats.total_visits;
                    if(document.getElementById('stats-completed')) document.getElementById('stats-completed').innerText = stats.completed;
                    if(document.getElementById('stats-pending')) document.getElementById('stats-pending').innerText = stats.pending;
                    if(document.getElementById('stats-cancelled')) document.getElementById('stats-cancelled').innerText = stats.cancelled;
                }

                // 2. Update Active Queue Card
                const wrapper = document.getElementById('active-queue-wrapper');
                if (wrapper) {
                    if (data.active_queue) {
                        const q = data.active_queue;
                        const currentStatus = q.status;
                        
                        // Alert Logic
                        if (!initialLoad && lastStatus && currentStatus !== lastStatus) {
                            if (currentStatus === 'called') {
                                Swal.fire({
                                    icon: 'success',
                                    title: "🎉 It's Your Turn!",
                                    html: `
                                        <p class="fs-2 fw-bold text-success mb-2">${q.queue_number}</p>
                                        <p>Please proceed to <strong>${q.counter}</strong></p>
                                        <p class="small text-muted">Service: ${q.service}</p>
                                    `,
                                    confirmButtonColor: '#20C997',
                                    confirmButtonText: "I'm On My Way!",
                                    allowOutsideClick: false
                                });
                            } else if (currentStatus === 'serving') {
                                Swal.fire({
                                    icon: 'info',
                                    title: 'Now Being Served',
                                    text: 'You are currently being served.',
                                    confirmButtonColor: '#20C997',
                                    timer: 3000
                                });
                            }
                        }
                        lastStatus = currentStatus;

                        // Render Card
                        let statusHtml = '';
                        if (q.status === 'waiting') {
                            statusHtml = `
                                <span class="badge bg-warning text-dark px-3 py-2 fs-6">
                                    <i class="fas fa-hourglass-half me-1"></i> Waiting
                                </span>
                                <p class="mb-0 opacity-75 small mt-2">Please wait for your number to be called</p>`;
                        } else if (q.status === 'called') {
                            statusHtml = `
                                <span class="badge bg-success text-white px-3 py-2 fs-6 animate-pulse">
                                    <i class="fas fa-bullhorn me-1"></i> YOUR TURN!
                                </span>
                                <p class="mb-0 mt-2">Please proceed to <strong>${q.counter}</strong></p>`;
                        } else if (q.status === 'serving') {
                            statusHtml = `
                                <span class="badge bg-info text-white px-3 py-2 fs-6">
                                    <i class="fas fa-user-md me-1"></i> Being Served
                                </span>
                                <p class="mb-0 opacity-75 mt-2">At <strong>${q.counter}</strong></p>`;
                        }

                        wrapper.innerHTML = `
                            <div class="patient-card mb-4 p-0 overflow-hidden" id="activeQueueCard">
                                <div class="p-4" style="background: linear-gradient(135deg, #20C997 0%, #0dcaf0 100%);">
                                    <div class="row align-items-center text-white">
                                        <div class="col-md-6">
                                            <span class="badge bg-white bg-opacity-25 text-white mb-2">
                                                <i class="fas fa-clock me-1"></i> Active Queue
                                            </span>
                                            <div class="display-4 fw-bold mb-2">${q.queue_number}</div>
                                            <p class="mb-0 opacity-75">
                                                <i class="fas fa-stethoscope me-1"></i> <span>${q.service}</span>
                                            </p>
                                        </div>
                                        <div class="col-md-6 text-md-end mt-3 mt-md-0">
                                            ${statusHtml}
                                        </div>
                                    </div>
                                </div>
                            </div>`;
                    } else {
                        // Render Empty State
                        wrapper.innerHTML = `
                            <div class="patient-card p-4 text-center mb-4">
                                <div class="py-3">
                                    <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                                        <i class="fas fa-ticket-alt text-muted fa-2x"></i>
                                    </div>
                                    <h5 class="fw-bold mb-2">No Active Queue</h5>
                                    <p class="text-muted small mb-3">You don't have a queue ticket for today yet.</p>
                                    <a href="{{ route('patient.request-appointment') }}" class="btn btn-primary">
                                        <i class="fas fa-calendar-plus me-1"></i> Request Appointment
                                    </a>
                                </div>
                            </div>`;
                        lastStatus = null;
                    }
                }

                // 3. Update Recent History
                const historyContainer = document.getElementById('recent-appointments-container');
                if (historyContainer && data.history) {
                    if (data.history.length > 0) {
                        const rows = data.history.map(item => {
                            let statusClass = 'secondary';
                            if(item.status_raw === 'completed') statusClass = 'success';
                            else if(item.status_raw === 'cancelled' || item.status_raw === 'rejected') statusClass = 'danger';
                            else if(item.status_raw === 'skipped' || item.status_raw === 'pending' || item.status_raw === 'pending-request') statusClass = 'warning';
                            else statusClass = 'info'; // waiting/active

                            return `
                                <tr>
                                    <td class="ps-3"><span class="fw-bold text-success">${item.queue_number}</span></td>
                                    <td>${item.service}</td>
                                    <td class="text-muted small">${item.created_at}</td>
                                    <td class="text-center"><span class="badge bg-${statusClass} bg-opacity-10 text-${statusClass}">${item.status}</span></td>
                                </tr>
                            `;
                        }).join('');

                        historyContainer.innerHTML = `
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="border-0 ps-3">Queue #</th>
                                            <th class="border-0">Service</th>
                                            <th class="border-0">Date</th>
                                            <th class="border-0 text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>${rows}</tbody>
                                </table>
                            </div>`;
                    } else {
                         historyContainer.innerHTML = `
                            <div class="text-center py-5">
                                <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                                    <i class="fas fa-history text-muted"></i>
                                </div>
                                <p class="text-muted mb-0">No recent appointments</p>
                            </div>
                         `;
                    }
                }
                
                initialLoad = false;
            })
            .catch(e => console.error('Dashboard update failed:', e));
    }

    // Start polling
    document.addEventListener('DOMContentLoaded', () => {
        // Initial call
        // updateDashboard(); // Optional, let page load first
        setInterval(updateDashboard, 5000);
    });
</script>
@endpush
```
