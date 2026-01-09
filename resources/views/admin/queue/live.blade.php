@extends('layouts.admin')

@section('title', 'Live Queue - HealthQueue')

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">
            <span class="d-inline-block rounded-circle bg-success me-2" style="width: 10px; height: 10px; animation: pulse 2s infinite;"></span>
            Live Monitoring
        </h4>
        <p class="text-muted small mb-0">Real-time queue monitoring</p>
    </div>
    <a href="{{ route('display') }}" target="_blank" class="btn btn-outline-primary btn-sm">
        <i class="fas fa-tv me-1"></i> Display Screen
    </a>
</div>

<style>
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }
</style>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <small class="text-muted text-uppercase d-block mb-1" style="font-size: 0.65rem;">Waiting</small>
                <h5 class="fw-bold text-warning mb-0" id="waitingCount">0</h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <small class="text-muted text-uppercase d-block mb-1" style="font-size: 0.65rem;">Called</small>
                <h5 class="fw-bold text-primary mb-0" id="calledCount">0</h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <small class="text-muted text-uppercase d-block mb-1" style="font-size: 0.65rem;">Serving</small>
                <h5 class="fw-bold text-success mb-0" id="servingCount">0</h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <small class="text-muted text-uppercase d-block mb-1" style="font-size: 0.65rem;">Completed</small>
                <h5 class="fw-bold text-info mb-0" id="completedCount">0</h5>
            </div>
        </div>
    </div>
</div>

<!-- Filter Tabs -->
<div class="btn-group btn-group-sm mb-3" id="queueTabs">
    <button class="btn btn-outline-secondary active" data-filter="all">All</button>
    <button class="btn btn-outline-secondary" data-filter="waiting">Waiting</button>
    <button class="btn btn-outline-secondary" data-filter="called">Called</button>
    <button class="btn btn-outline-secondary" data-filter="serving">Serving</button>
</div>

<!-- Queue Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0">Active Queues</h6>
        <input type="text" class="form-control form-control-sm" id="searchQueue" placeholder="Search..." style="max-width: 150px;">
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center" style="font-size: 0.8rem;">
                <thead class="bg-light">
                    <tr>
                        <th class="border-bottom-0">Queue #</th>
                        <th class="border-bottom-0">Patient</th>
                        <th class="border-bottom-0">Service</th>
                        <th class="border-bottom-0">Priority</th>
                        <th class="border-bottom-0">Status</th>
                        <th class="border-bottom-0">Counter</th>
                        <th class="border-bottom-0">Wait</th>
                        <th class="border-bottom-0">Actions</th>
                    </tr>
                </thead>
                <tbody id="queueTableBody">
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fas fa-spinner fa-spin mb-2"></i>
                            <p class="mb-0 small">Loading...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentFilter = 'all';
    
    function loadQueues() {
        Promise.all([
            fetch('/staff/queue/waiting').then(r => r.json()),
            fetch('/staff/called').then(r => r.json()),
            fetch('/staff/serving').then(r => r.json())
        ]).then(([waiting, called, serving]) => {
            const allQueues = [
                ...(waiting.data || []).map(q => ({...q, status: 'waiting'})),
                ...(called.data || []).map(q => ({...q, status: 'called'})),
                ...(serving.data || []).map(q => ({...q, status: 'serving'}))
            ];
            
            document.getElementById('waitingCount').textContent = allQueues.filter(q => q.status === 'waiting').length;
            document.getElementById('calledCount').textContent = allQueues.filter(q => q.status === 'called').length;
            document.getElementById('servingCount').textContent = allQueues.filter(q => q.status === 'serving').length;
            
            fetch('/staff/queue/stats').then(r => r.json()).then(data => {
                document.getElementById('completedCount').textContent = data.data?.completed || 0;
            });
            
            renderQueues(allQueues.filter(q => currentFilter === 'all' || q.status === currentFilter));
        });
    }
    
    function formatWaitTime(minutes) {
        if (minutes === undefined || minutes === null) return '-';
        const totalMinutes = Math.floor(parseFloat(minutes));
        const hrs = Math.floor(totalMinutes / 60);
        const mins = totalMinutes % 60;
        
        if (hrs > 0) {
            return `${hrs}h ${mins}m`;
        }
        return `${mins}m`;
    }

    function renderQueues(queues) {
        const tbody = document.getElementById('queueTableBody');
        if (queues.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center py-5 text-muted"><i class="fas fa-inbox fa-2x mb-2 opacity-25"></i><p class="mb-0 small">No queues found</p></td></tr>';
            return;
        }
        
        tbody.innerHTML = queues.map(q => `
            <tr>
                <td class="fw-bold">${q.queue_number}</td>
                <td>${q.patient_name || q.patient?.name || 'N/A'}</td>
                <td><span class="badge" style="background: ${q.service?.color || '#6c757d'}; color: white;">${q.service?.name || q.service || '-'}</span></td>
                <td><span class="badge ${q.priority_code === 'EMG' ? 'bg-danger' : q.priority_code === 'SNR' ? 'bg-purple' : 'bg-light text-dark'}">${q.priority_code || q.priority?.code || 'REG'}</span></td>
                <td><span class="badge ${q.status === 'waiting' ? 'bg-warning text-dark' : q.status === 'called' ? 'bg-primary' : 'bg-success'}">${q.status.toUpperCase()}</span></td>
                <td class="text-muted">${q.counter?.name || q.counter || '-'}</td>
                <td class="text-muted fw-bold">${formatWaitTime(q.wait_time)}</td>
                <td>
                    <div class="btn-group btn-group-sm">
                        ${q.status === 'waiting' ? `
                            <button class="btn btn-outline-primary" onclick="callQueue(${q.id})" title="Call Next">
                                <i class="fas fa-bullhorn"></i>
                            </button>
                            <button class="btn btn-outline-danger" onclick="cancelQueue(${q.id})" title="Cancel">
                                <i class="fas fa-times"></i>
                            </button>
                        ` : ''}
                        
                        ${q.status === 'called' ? `
                            <button class="btn btn-outline-success" onclick="startServing(${q.id})" title="Start Serving">
                                <i class="fas fa-play"></i>
                            </button>
                            <button class="btn btn-outline-info" onclick="recallQueue(${q.id})" title="Announce Again (Recall)">
                                <i class="fas fa-volume-up"></i>
                            </button>
                            <button class="btn btn-outline-secondary" onclick="skipQueue(${q.id})" title="Skip">
                                <i class="fas fa-forward"></i>
                            </button>
                            <button class="btn btn-outline-danger" onclick="cancelQueue(${q.id})" title="Cancel">
                                <i class="fas fa-times"></i>
                            </button>
                        ` : ''}
                        
                        ${q.status === 'serving' ? `
                            <button class="btn btn-outline-success" onclick="completeQueue(${q.id})" title="Complete">
                                <i class="fas fa-check"></i>
                            </button>
                        ` : ''}
                    </div>
                </td>
            </tr>
        `).join('');
    }
    
    document.querySelectorAll('#queueTabs button').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('#queueTabs button').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentFilter = this.dataset.filter;
            loadQueues();
        });
    });
    
    loadQueues();
    setInterval(loadQueues, 3000);
    
    // Helper for SweetAlert Actions
    const performAction = (url, method = 'POST', confirmOptions = null) => {
        const execute = () => {
            fetch(url, { 
                method: method, 
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } 
            })
            .then(r => r.json())
            .then(data => {
                if(data.success) {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });
                    Toast.fire({ icon: 'success', title: data.message || 'Success' });
                    loadQueues();
                } else {
                    Swal.fire('Error', data.message || 'Action failed', 'error');
                }
            })
            .catch(err => Swal.fire('Error', 'Connection error', 'error'));
        };

        if (confirmOptions) {
            Swal.fire({
                title: confirmOptions.title || 'Are you sure?',
                text: confirmOptions.text || '',
                icon: confirmOptions.icon || 'warning',
                showCancelButton: true,
                confirmButtonColor: confirmOptions.confirmColor || '#3085d6',
                confirmButtonText: 'Yes, proceed',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) execute();
            });
        } else {
            execute();
        }
    };

    // Action Handlers
    window.callQueue = id => performAction(`/staff/queue/${id}/call`);
    window.recallQueue = id => performAction(`/staff/queue/${id}/recall`); // Announce Function
    window.startServing = id => performAction(`/staff/queue/${id}/start`);
    
    window.skipQueue = id => performAction(`/staff/queue/${id}/skip`, 'POST', {
        title: 'Skip Patient?',
        text: 'Patient will be moved to skipped status.',
        confirmColor: '#6c757d',
        icon: 'warning'
    });
    
    window.completeQueue = id => performAction(`/staff/queue/${id}/complete`, 'POST', {
        title: 'Complete Service?',
        text: 'Mark this transaction as completed?',
        confirmColor: '#198754',
        icon: 'question'
    });
    
    window.cancelQueue = id => performAction(`/staff/queue/${id}/cancel`, 'POST', {
        title: 'Cancel Queue?',
        text: 'This action cannot be undone.',
        confirmColor: '#dc3545',
        icon: 'warning'
    });
});
</script>
@endpush
