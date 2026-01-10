@extends('layouts.patient')

@section('title', 'Live Display - Smart Healthcare')

@section('content')
<!-- Page Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Live Queue Display</h4>
        <p class="text-muted mb-0">Real-time queue status across all services</p>
    </div>
    <div class="d-flex align-items-center gap-3">
        <span class="badge bg-success bg-opacity-10 text-success px-3 py-2">
            <i class="fas fa-circle text-success me-1" style="font-size: 8px;"></i> Live
        </span>
        <span class="text-muted small" id="timestamp">{{ now()->format('H:i:s') }}</span>
    </div>
</div>

<!-- My Queue Highlight (if active) -->
@if($patient)
<div id="myQueueSection">
    <!-- Will be populated by JS -->
</div>
@endif

<div class="row">
    <!-- Now Serving -->
    <div class="col-lg-6 mb-4">
        <div class="patient-card h-100">
            <div class="p-3 border-bottom bg-success bg-opacity-10">
                <h6 class="fw-bold mb-0 text-success">
                    <i class="fas fa-bullhorn me-2"></i>Now Serving
                </h6>
            </div>
            <div class="p-3" id="nowServingList">
                @forelse($nowServing as $queue)
                <div class="d-flex align-items-center justify-content-between p-3 mb-2 rounded-3 {{ $patient && $patient->id == $queue->patient_id ? 'bg-success bg-opacity-10 border border-success' : 'bg-light' }}">
                    <div>
                        <span class="display-6 fw-bold {{ $patient && $patient->id == $queue->patient_id ? 'text-success' : 'text-primary' }}">{{ $queue->queue_number }}</span>
                        @if($patient && $patient->id == $queue->patient_id)
                            <span class="badge bg-success ms-2">You</span>
                        @endif
                    </div>
                    <div class="text-end">
                        <div class="fw-medium">{{ $queue->counter?->name ?? 'Counter' }}</div>
                        <div class="text-muted small">{{ $queue->service->name }}</div>
                    </div>
                </div>
                @empty
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-users-slash fa-2x mb-3"></i>
                    <p class="mb-0">No one being served at the moment</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
    
    <!-- Coming Up Next -->
    <div class="col-lg-6 mb-4">
        <div class="patient-card h-100">
            <div class="p-3 border-bottom bg-warning bg-opacity-10">
                <h6 class="fw-bold mb-0 text-warning">
                    <i class="fas fa-clock me-2"></i>Coming Up Next
                </h6>
            </div>
            <div class="p-3" id="waitingList">
                @forelse($waitingQueues as $index => $queue)
                <div class="d-flex align-items-center justify-content-between p-2 mb-1 rounded {{ $patient && $patient->id == $queue->patient_id ? 'bg-warning bg-opacity-10 border border-warning' : '' }}">
                    <div class="d-flex align-items-center gap-3">
                        <span class="badge bg-secondary rounded-pill" style="width: 28px;">{{ $index + 1 }}</span>
                        <span class="fw-bold {{ $patient && $patient->id == $queue->patient_id ? 'text-warning' : '' }}">{{ $queue->queue_number }}</span>
                        @if($patient && $patient->id == $queue->patient_id)
                            <span class="badge bg-warning text-dark">You</span>
                        @endif
                    </div>
                    <span class="text-muted small">{{ $queue->service->name }}</span>
                </div>
                @empty
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-check-circle fa-2x mb-3"></i>
                    <p class="mb-0">No one waiting in queue</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Stats -->
<div class="row">
    <div class="col-4">
        <div class="patient-card p-3 text-center">
            <div class="fs-2 fw-bold text-success" id="servingCount">{{ $nowServing->count() }}</div>
            <div class="text-muted small">Being Served</div>
        </div>
    </div>
    <div class="col-4">
        <div class="patient-card p-3 text-center">
            <div class="fs-2 fw-bold text-warning" id="waitingCount">{{ $waitingQueues->count() }}</div>
            <div class="text-muted small">Waiting</div>
        </div>
    </div>
    <div class="col-4">
        <div class="patient-card p-3 text-center">
            <div class="fs-2 fw-bold text-info" id="totalCount">{{ $nowServing->count() + $waitingQueues->count() }}</div>
            <div class="text-muted small">Total Active</div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let lastMyQueueStatus = null;
    
    function updateDisplay() {
        fetch('{{ route("patient.api.queue-data") }}')
            .then(r => r.json())
            .then(data => {
                if (!data.success) return;
                
                // Update timestamp
                document.getElementById('timestamp').textContent = data.timestamp;
                
                // Update now serving
                let nowServingHtml = '';
                if (data.now_serving.length > 0) {
                    data.now_serving.forEach(q => {
                        const isMe = data.my_queue && q.queue_number === data.my_queue.queue_number;
                        nowServingHtml += `
                            <div class="d-flex align-items-center justify-content-between p-3 mb-2 rounded-3 ${isMe ? 'bg-success bg-opacity-10 border border-success' : 'bg-light'}">
                                <div>
                                    <span class="display-6 fw-bold ${isMe ? 'text-success' : 'text-primary'}">${q.queue_number}</span>
                                    ${isMe ? '<span class="badge bg-success ms-2">You</span>' : ''}
                                </div>
                                <div class="text-end">
                                    <div class="fw-medium">${q.counter}</div>
                                    <div class="text-muted small">${q.service}</div>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    nowServingHtml = `
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-users-slash fa-2x mb-3"></i>
                            <p class="mb-0">No one being served at the moment</p>
                        </div>
                    `;
                }
                document.getElementById('nowServingList').innerHTML = nowServingHtml;
                
                // Update waiting list
                let waitingHtml = '';
                if (data.waiting.length > 0) {
                    data.waiting.forEach((q, index) => {
                        const isMe = data.my_queue && q.queue_number === data.my_queue.queue_number;
                        waitingHtml += `
                            <div class="d-flex align-items-center justify-content-between p-2 mb-1 rounded ${isMe ? 'bg-warning bg-opacity-10 border border-warning' : ''}">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="badge bg-secondary rounded-pill" style="width: 28px;">${index + 1}</span>
                                    <span class="fw-bold ${isMe ? 'text-warning' : ''}">${q.queue_number}</span>
                                    ${isMe ? '<span class="badge bg-warning text-dark">You</span>' : ''}
                                </div>
                                <span class="text-muted small">${q.service}</span>
                            </div>
                        `;
                    });
                } else {
                    waitingHtml = `
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-check-circle fa-2x mb-3"></i>
                            <p class="mb-0">No one waiting in queue</p>
                        </div>
                    `;
                }
                document.getElementById('waitingList').innerHTML = waitingHtml;
                
                // Update counts
                document.getElementById('servingCount').textContent = data.now_serving.length;
                document.getElementById('waitingCount').textContent = data.waiting.length;
                document.getElementById('totalCount').textContent = data.now_serving.length + data.waiting.length;
                
                // Check if my queue status changed - show SweetAlert
                if (data.my_queue) {
                    if (lastMyQueueStatus && lastMyQueueStatus !== data.my_queue.status) {
                        if (data.my_queue.status === 'called') {
                            Swal.fire({
                                icon: 'success',
                                title: "🎉 It's Your Turn!",
                                html: `
                                    <p class="fs-1 fw-bold text-success">${data.my_queue.queue_number}</p>
                                    <p>Please proceed to <strong>${data.my_queue.counter || 'the Counter'}</strong></p>
                                `,
                                confirmButtonColor: '#20C997',
                                confirmButtonText: "I'm Coming!"
                            });
                        } else if (data.my_queue.status === 'serving') {
                            Swal.fire({
                                icon: 'info',
                                title: 'Now Being Served',
                                text: 'You are currently being served.',
                                confirmButtonColor: '#20C997',
                                timer: 3000
                            });
                        }
                    }
                    lastMyQueueStatus = data.my_queue.status;
                    
                    // Update my queue section
                    const myQueueSection = document.getElementById('myQueueSection');
                    if (myQueueSection) {
                        myQueueSection.innerHTML = `
                            <div class="alert border-0 mb-4 ${data.my_queue.status === 'called' ? 'bg-success text-white' : 'bg-success bg-opacity-10 text-success'}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><i class="fas fa-ticket-alt me-2"></i>Your Queue:</strong>
                                        <span class="fs-5 fw-bold ms-2">${data.my_queue.queue_number}</span>
                                    </div>
                                    <div>
                                        ${data.my_queue.status === 'waiting' 
                                            ? `<span class="badge bg-warning text-dark">Position: ${data.my_queue.position}</span>` 
                                            : data.my_queue.status === 'called'
                                                ? `<span class="badge bg-light text-success">YOUR TURN - ${data.my_queue.counter || 'Counter'}</span>`
                                                : `<span class="badge bg-info">Being Served</span>`
                                        }
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                }
            })
            .catch(e => console.error('Failed to update display:', e));
    }
    
    // Initial load
    @if($patient)
    lastMyQueueStatus = '{{ $nowServing->firstWhere("patient_id", $patient->id)?->status ?? $waitingQueues->firstWhere("patient_id", $patient->id)?->status ?? "" }}';
    @endif
    
    // Poll every 5 seconds
    setInterval(updateDisplay, 5000);
</script>
@endpush
