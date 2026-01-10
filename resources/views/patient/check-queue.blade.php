@extends('layouts.patient')

@section('title', 'Check Queue Status - Smart Healthcare')

@section('content')
<!-- Page Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Check Queue Status</h4>
        <p class="text-muted mb-0">Real-time tracking of your queue position</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-success bg-opacity-10 text-success px-3 py-2">
            <i class="fas fa-circle text-success me-1" style="font-size: 8px;"></i> Live
        </span>
    </div>
</div>

<!-- My Queue Status -->
<div class="row">
    <div class="col-lg-8">
        @if($activeQueue)
        <div class="patient-card p-0 overflow-hidden mb-4" id="myQueueCard">
            <div class="p-4" style="background: linear-gradient(135deg, #20C997 0%, #0dcaf0 100%);">
                <div class="row align-items-center text-white">
                    <div class="col-md-6">
                        <span class="badge bg-white bg-opacity-25 text-white mb-2">
                            <i class="fas fa-ticket-alt me-1"></i> Your Queue Number
                        </span>
                        <div class="display-3 fw-bold mb-2" id="queueNumber">{{ $activeQueue->queue_number }}</div>
                        <p class="mb-0 opacity-75">
                            <i class="fas fa-stethoscope me-1"></i> <span id="serviceName">{{ $activeQueue->service->name }}</span>
                        </p>
                    </div>
                    <div class="col-md-6 text-md-end mt-3 mt-md-0">
                        <div id="statusBadge">
                            @if($activeQueue->status === 'waiting')
                                <span class="badge bg-warning text-dark px-4 py-3 fs-5">
                                    <i class="fas fa-hourglass-half me-1"></i> Waiting
                                </span>
                                <div class="mt-3">
                                    <div class="opacity-75 small">Position in Queue</div>
                                    <div class="display-6 fw-bold" id="positionNumber">{{ $position }}</div>
                                </div>
                            @elseif($activeQueue->status === 'called')
                                <span class="badge bg-success text-white px-4 py-3 fs-5 animate-pulse">
                                    <i class="fas fa-bullhorn me-1"></i> YOUR TURN!
                                </span>
                                <div class="mt-3 fs-5">
                                    Please proceed to <strong id="counterName">{{ $activeQueue->counter?->name ?? 'Counter' }}</strong>
                                </div>
                            @elseif($activeQueue->status === 'serving')
                                <span class="badge bg-info text-white px-4 py-3 fs-5">
                                    <i class="fas fa-user-md me-1"></i> Being Served
                                </span>
                                <div class="mt-3">
                                    At <strong id="counterName">{{ $activeQueue->counter?->name ?? 'Counter' }}</strong>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Timeline -->
            <div class="p-4 bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-center flex-fill">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center bg-success text-white" style="width: 40px; height: 40px;">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="small text-muted mt-2">Registered</div>
                    </div>
                    <div class="flex-fill border-top" style="height: 2px; background: {{ $activeQueue->status !== 'waiting' ? '#20C997' : '#dee2e6' }};"></div>
                    <div class="text-center flex-fill">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center {{ $activeQueue->status !== 'waiting' ? 'bg-success text-white' : 'bg-light text-muted' }}" style="width: 40px; height: 40px;">
                            @if($activeQueue->status !== 'waiting')
                                <i class="fas fa-check"></i>
                            @else
                                <i class="fas fa-hourglass-half"></i>
                            @endif
                        </div>
                        <div class="small text-muted mt-2">Called</div>
                    </div>
                    <div class="flex-fill border-top" style="height: 2px; background: {{ $activeQueue->status === 'serving' ? '#20C997' : '#dee2e6' }};"></div>
                    <div class="text-center flex-fill">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center {{ $activeQueue->status === 'serving' ? 'bg-success text-white' : 'bg-light text-muted' }}" style="width: 40px; height: 40px;">
                            @if($activeQueue->status === 'serving')
                                <i class="fas fa-check"></i>
                            @else
                                <i class="fas fa-user-md"></i>
                            @endif
                        </div>
                        <div class="small text-muted mt-2">Serving</div>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="patient-card p-5 text-center mb-4">
            <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                <i class="fas fa-ticket-alt text-muted fa-2x"></i>
            </div>
            <h5 class="fw-bold mb-2">No Active Queue</h5>
            <p class="text-muted mb-3">You don't have any active queue at the moment.</p>
            <a href="{{ route('patient.request-appointment') }}" class="btn btn-primary">
                <i class="fas fa-calendar-plus me-1"></i> Request Appointment
            </a>
        </div>
        @endif
    </div>
    
    <div class="col-lg-4">
        <!-- Quick Info Card -->
        <div class="patient-card p-4">
            <h6 class="fw-bold mb-3">
                <i class="fas fa-info-circle text-info me-2"></i>Queue Information
            </h6>
            
            <div class="mb-3 pb-3 border-bottom">
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">Last Updated</span>
                    <span class="fw-medium small" id="lastUpdated">{{ now()->format('H:i:s') }}</span>
                </div>
            </div>
            
            @if($activeQueue)
            <div class="mb-3 pb-3 border-bottom">
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">Joined At</span>
                    <span class="fw-medium small">{{ $activeQueue->created_at->format('g:i A') }}</span>
                </div>
            </div>
            <div class="mb-3">
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">Priority</span>
                    <span class="badge" style="background: {{ $activeQueue->priority->color ?? '#6c757d' }}20; color: {{ $activeQueue->priority->color ?? '#6c757d' }};">
                        {{ $activeQueue->priority->name ?? 'Regular' }}
                    </span>
                </div>
            </div>
            @endif
            
            <div class="alert alert-light border small mb-0">
                <i class="fas fa-bell text-warning me-2"></i>
                Stay on this page to receive real-time updates about your queue status.
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    @keyframes pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.8; transform: scale(1.02); }
    }
    
    .animate-pulse {
        animation: pulse 1.5s ease-in-out infinite;
    }
</style>
@endpush

@push('scripts')
<script>
    let lastStatus = '{{ $activeQueue?->status ?? "" }}';
    
    // Update status badge without reload
    function updateStatusBadge(q) {
        const statusBadge = document.getElementById('statusBadge');
        if (!statusBadge) return;
        
        let html = '';
        if (q.status === 'waiting') {
            html = `
                <span class="badge bg-warning text-dark px-4 py-3 fs-5">
                    <i class="fas fa-hourglass-half me-1"></i> Waiting
                </span>
                <div class="mt-3">
                    <div class="opacity-75 small">Position in Queue</div>
                    <div class="display-6 fw-bold" id="positionNumber">${q.position}</div>
                </div>
            `;
        } else if (q.status === 'called') {
            html = `
                <span class="badge bg-success text-white px-4 py-3 fs-5 animate-pulse">
                    <i class="fas fa-bullhorn me-1"></i> YOUR TURN!
                </span>
                <div class="mt-3 fs-5">
                    Please proceed to <strong>${q.counter || 'Counter'}</strong>
                </div>
            `;
        } else if (q.status === 'serving') {
            html = `
                <span class="badge bg-info text-white px-4 py-3 fs-5">
                    <i class="fas fa-user-md me-1"></i> Being Served
                </span>
                <div class="mt-3">
                    At <strong>${q.counter || 'Counter'}</strong>
                </div>
            `;
        }
        statusBadge.innerHTML = html;
    }
    
    // Update timeline without reload
    function updateTimeline(status) {
        const timeline = document.querySelector('.p-4.bg-white');
        if (!timeline) return;
        
        const calledLine = timeline.querySelectorAll('.flex-fill.border-top')[0];
        const servingLine = timeline.querySelectorAll('.flex-fill.border-top')[1];
        const calledCircle = timeline.querySelectorAll('.rounded-circle')[1];
        const servingCircle = timeline.querySelectorAll('.rounded-circle')[2];
        
        if (status !== 'waiting') {
            calledLine.style.background = '#20C997';
            calledCircle.className = 'rounded-circle d-inline-flex align-items-center justify-content-center bg-success text-white';
            calledCircle.innerHTML = '<i class="fas fa-check"></i>';
        }
        
        if (status === 'serving') {
            servingLine.style.background = '#20C997';
            servingCircle.className = 'rounded-circle d-inline-flex align-items-center justify-content-center bg-success text-white';
            servingCircle.innerHTML = '<i class="fas fa-check"></i>';
        }
    }
    
    // Real-time queue status polling (NO PAGE RELOAD)
    function updateQueueStatus() {
        fetch('{{ route("patient.queue-status") }}')
            .then(r => r.json())
            .then(data => {
                document.getElementById('lastUpdated').textContent = new Date().toLocaleTimeString();
                
                if (data.success && data.data) {
                    const q = data.data;
                    
                    // Check if status changed - show SweetAlert
                    if (lastStatus && q.status !== lastStatus) {
                        if (q.status === 'called') {
                            Swal.fire({
                                icon: 'success',
                                title: "🎉 It's Your Turn!",
                                html: `
                                    <p class="fs-2 fw-bold text-success mb-2">${q.queue_number}</p>
                                    <p>Please proceed to <strong>${q.counter || 'Counter'}</strong></p>
                                `,
                                confirmButtonColor: '#20C997',
                                confirmButtonText: 'Got it!'
                            });
                        } else if (q.status === 'serving') {
                            Swal.fire({
                                icon: 'info',
                                title: 'Now Being Served',
                                text: 'You are now being served.',
                                confirmButtonColor: '#20C997',
                                timer: 3000
                            });
                        }
                    }
                    
                    // Update DOM without reload
                    updateStatusBadge(q);
                    updateTimeline(q.status);
                    lastStatus = q.status;
                }
            })
            .catch(e => console.error('Status check failed:', e));
    }
    
    // Poll every 5 seconds
    setInterval(updateQueueStatus, 5000);
</script>
@endpush
