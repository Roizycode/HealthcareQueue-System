@extends('layouts.staff')

@section('title', 'Live Monitor - Smart Healthcare')

@section('content')
<!-- Page Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">
            <span class="d-inline-block rounded-circle bg-success me-2" style="width: 10px; height: 10px; animation: pulse 2s infinite;"></span>
            Live Queue Monitor
        </h4>
        <p class="text-muted small mb-0">Real-time queue status (Staff View)</p>
    </div>
    <div class="d-flex align-items-center gap-3">
        <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 border border-success border-opacity-25 rounded-pill">
            <i class="fas fa-circle text-success me-1" style="font-size: 8px;"></i> Live
        </span>
        <span class="text-muted small fw-mono" id="timestamp">{{ now()->format('H:i:s') }}</span>
    </div>
</div>

<style>
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }
.fw-mono { font-family: monospace; }
.monitor-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    background: white;
    height: 100%;
}
.monitor-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid rgba(0,0,0,0.05);
}
.monitor-body {
    padding: 1.5rem;
    min-height: 400px;
    max-height: 600px;
    overflow-y: auto;
}
/* Custom Scrollbar for Monitor Body */
.monitor-body::-webkit-scrollbar {
    width: 6px;
}
.monitor-body::-webkit-scrollbar-track {
    background: #f1f1f1;
}
.monitor-body::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 3px;
}
</style>

<div class="row g-4">
    <!-- Now Serving -->
    <div class="col-lg-6">
        <div class="monitor-card">
            <div class="monitor-header bg-success bg-opacity-10 text-success">
                <h6 class="fw-bold mb-0">
                    <i class="fas fa-bullhorn me-2"></i>Now Serving
                </h6>
            </div>
            <div class="monitor-body" id="nowServingList">
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
                    <p class="mb-0">Loading...</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Coming Up Next -->
    <div class="col-lg-6">
        <div class="monitor-card">
            <div class="monitor-header bg-warning bg-opacity-10 text-warning">
                <h6 class="fw-bold mb-0">
                    <i class="fas fa-clock me-2"></i>Coming Up Next
                </h6>
            </div>
            <div class="monitor-body" id="waitingList">
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
                    <p class="mb-0">Loading...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body">
                <h2 class="fw-bold text-success mb-0" id="servingCount">-</h2>
                <small class="text-muted text-uppercase">Being Served</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body">
                <h2 class="fw-bold text-warning mb-0" id="waitingCount">-</h2>
                <small class="text-muted text-uppercase">Waiting</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body">
                <h2 class="fw-bold text-primary mb-0" id="totalCount">-</h2>
                <small class="text-muted text-uppercase">Total Active</small>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function updateMonitor() {
        fetch('{{ route("staff.api.live-queue") }}')
            .then(r => r.json())
            .then(data => {
                if (!data.success) return;
                
                // Timestamp
                document.getElementById('timestamp').textContent = data.timestamp;
                
                // Now Serving
                let nowServingHtml = '';
                if (data.now_serving.length > 0) {
                    data.now_serving.forEach(q => {
                        const statusColor = q.status === 'called' ? 'warning' : 'success';
                        const statusBadge = q.status === 'called' ? 'CALLING' : 'SERVING';
                        
                        nowServingHtml += `
                            <div class="d-flex align-items-center justify-content-between p-3 mb-2 rounded-3 bg-light border-start border-5 border-${statusColor}">
                                <div>
                                    <span class="display-6 fw-bold text-dark">${q.queue_number}</span>
                                    <span class="badge bg-${statusColor} ms-2 small" style="vertical-align: middle;">${statusBadge}</span>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-dark">${q.counter || 'Counter'}</div>
                                    <div class="text-muted small">${q.service}</div>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    nowServingHtml = `
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-mug-hot fa-2x mb-3 opacity-25"></i>
                            <p class="mb-0">No queues active</p>
                        </div>
                    `;
                }
                document.getElementById('nowServingList').innerHTML = nowServingHtml;
                
                // Waiting List
                let waitingHtml = '';
                if (data.waiting.length > 0) {
                    data.waiting.forEach((q, index) => {
                        waitingHtml += `
                            <div class="d-flex align-items-center justify-content-between p-2 mb-1 rounded border-bottom">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="badge bg-secondary bg-opacity-25 text-dark rounded-pill" style="width: 25px; height: 25px; display: flex; align-items: center; justify-content: center;">${index + 1}</span>
                                    <span class="fw-bold text-dark fs-5">${q.queue_number}</span>
                                </div>
                                <span class="badge bg-light text-muted border">${q.service}</span>
                            </div>
                        `;
                    });
                } else {
                     waitingHtml = `
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-check-circle fa-2x mb-3 opacity-25"></i>
                            <p class="mb-0">Queue is empty</p>
                        </div>
                    `;
                }
                document.getElementById('waitingList').innerHTML = waitingHtml;
                
                // Counts
                document.getElementById('servingCount').textContent = data.now_serving.length;
                document.getElementById('waitingCount').textContent = data.waiting.length;
                document.getElementById('totalCount').textContent = data.now_serving.length + data.waiting.length;
            })
            .catch(e => console.error('Monitor update failed', e));
    }
    
    // Initial and poll
    updateMonitor();
    setInterval(updateMonitor, 3000);
</script>
@endpush
