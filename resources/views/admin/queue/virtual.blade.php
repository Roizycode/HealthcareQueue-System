@extends('layouts.admin')

@section('title', 'Virtual Queue - Smart Healthcare')

@push('styles')
<style>
    @media (max-width: 768px) {
        /* Mobile Receipt Table Styling */
        .table thead { display: none; }
        .table tbody tr {
            display: block;
            margin-bottom: 1rem;
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            padding: 1rem;
            position: relative;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
        }
        .table td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: none;
            padding: 0.5rem 0;
            text-align: right;
        }
        .table td::before {
            content: attr(data-label);
            font-weight: 600;
            color: #6c757d;
            font-size: 0.85rem;
            margin-right: 1rem;
            text-align: left;
        }
        .table td:last-child {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px dashed #dee2e6;
            justify-content: center;
        }
        .table td:last-child::before { display: none; }
    }
</style>
@endpush

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Virtual Queue</h4>
        <p class="text-muted small mb-0">Manage online and walk-in queue registrations</p>
    </div>
    <button class="btn btn-primary btn-sm" onclick="generateQR()">
        <i class="fas fa-qrcode me-1"></i> Generate QR
    </button>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <small class="text-muted text-uppercase d-block mb-1" style="font-size: 0.65rem;">Online Today</small>
                <h5 class="fw-bold text-primary mb-0">{{ $onlineCount ?? 0 }}</h5>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <small class="text-muted text-uppercase d-block mb-1" style="font-size: 0.65rem;">Walk-in Today</small>
                <h5 class="fw-bold text-success mb-0">{{ $walkinCount ?? 0 }}</h5>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <small class="text-muted text-uppercase d-block mb-1" style="font-size: 0.65rem;">Waiting</small>
                <h5 class="fw-bold text-warning mb-0">{{ $waitingCount ?? 0 }}</h5>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <small class="text-muted text-uppercase d-block mb-1" style="font-size: 0.65rem;">Completed</small>
                <h5 class="fw-bold text-info mb-0">{{ $completedCount ?? 0 }}</h5>
            </div>
        </div>
    </div>
</div>

<!-- Queue Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0">Recent Virtual Queues</h6>
        <div class="btn-group btn-group-sm" id="filterButtons">
            <a href="{{ route('admin.queue.virtual') }}" class="btn btn-outline-secondary {{ !request('type') ? 'active' : '' }}">All</a>
            <a href="{{ route('admin.queue.virtual', ['type' => 'online']) }}" class="btn btn-outline-secondary {{ request('type') === 'online' ? 'active' : '' }}">Online</a>
            <a href="{{ route('admin.queue.virtual', ['type' => 'walkin']) }}" class="btn btn-outline-secondary {{ request('type') === 'walkin' ? 'active' : '' }}">Walk-in</a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center" style="font-size: 0.8rem;">
                <thead class="bg-light">
                    <tr>
                        <th class="border-bottom-0">Queue #</th>
                        <th class="border-bottom-0">Patient</th>
                        <th class="border-bottom-0">Service</th>
                        <th class="border-bottom-0">Type</th>
                        <th class="border-bottom-0">Status</th>
                        <th class="border-bottom-0">Time</th>
                        <th class="border-bottom-0">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentQueues ?? [] as $queue)
                    <tr>
                        <td class="fw-bold" data-label="Queue #">{{ $queue->queue_number }}</td>
                        <td data-label="Patient">{{ $queue->patient->full_name ?? 'Guest' }}</td>
                        <td data-label="Service">
                            <span class="badge" style="background: {{ $queue->service->color ?? '#6c757d' }}; color: white;">{{ $queue->service->name ?? '-' }}</span>
                        </td>
                        <td data-label="Type">
                            @if($queue->type === 'online')
                                <span class="badge bg-primary"><i class="fas fa-globe me-1"></i>Online</span>
                            @else
                                <span class="badge bg-success"><i class="fas fa-walking me-1"></i>Walk-in</span>
                            @endif
                        </td>
                        <td data-label="Status">
                            @php
                                $statusColors = [
                                    'waiting' => 'bg-warning text-dark',
                                    'called' => 'bg-info',
                                    'serving' => 'bg-primary',
                                    'completed' => 'bg-success',
                                    'skipped' => 'bg-secondary',
                                    'cancelled' => 'bg-danger'
                                ];
                            @endphp
                            <span class="badge {{ $statusColors[$queue->status] ?? 'bg-light text-dark' }}">{{ ucfirst($queue->status) }}</span>
                        </td>
                        <td class="text-muted" data-label="Time">{{ $queue->created_at->format('h:i A') }}</td>
                        <td data-label="Actions">
                            <div class="btn-group btn-group-sm">
                                <!-- View button for all -->
                                <button class="btn btn-outline-secondary" onclick="viewDetails({{ $queue->id }})" title="View">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @if($queue->status === 'waiting')
                                    {{-- Notify Button --}}
                                    <button class="btn btn-primary" onclick="callQueue({{ $queue->id }})" title="Notify via Email">
                                        <i class="fas fa-envelope"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="cancelQueue({{ $queue->id }})" title="Cancel">
                                        <i class="fas fa-times"></i>
                                    </button>
                                @elseif($queue->status === 'called')
                                    <button class="btn btn-success" onclick="startServing({{ $queue->id }})" title="Start Serving">
                                        <i class="fas fa-play"></i>
                                    </button>
                                    <button class="btn btn-outline-secondary" onclick="skipQueue({{ $queue->id }})" title="Skip">
                                        <i class="fas fa-forward"></i>
                                    </button>
                                @elseif($queue->status === 'serving')
                                    <button class="btn btn-success" onclick="completeQueue({{ $queue->id }})" title="Complete">
                                        <i class="fas fa-check"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-mobile-alt fa-2x mb-2 opacity-25"></i>
                            <p class="mb-0 small">No queues found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if(isset($recentQueues) && $recentQueues->hasPages())
    <div class="card-footer bg-white py-2">
        {{ $recentQueues->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
const csrfToken = '{{ csrf_token() }}';

function generateQR() {
    Swal.fire({
        title: 'Virtual Queue QR Code',
        html: `<div class="text-center p-3">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode(route('queue.join')) }}" class="img-fluid mb-3">
            <p class="small text-muted mb-2">Scan to join queue online</p>
            <input type="text" class="form-control form-control-sm text-center" value="{{ route('queue.join') }}" readonly onclick="this.select()">
        </div>`,
        showCloseButton: true,
        showConfirmButton: false
    });
}

function callQueue(id) {
    Swal.fire({
        title: 'Notify Patient?',
        text: 'This will send an email notification to the patient.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0d6efd',
        confirmButtonText: '<i class="fas fa-envelope me-1"></i> Send Email'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/staff/queue/${id}/call`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken }
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Patient Notified!', timer: 1500, showConfirmButton: false })
                        .then(() => location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message });
                }
            });
        }
    });
}

function startServing(id) {
    fetch(`/staff/queue/${id}/start`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken }
    }).then(r => r.json()).then(data => {
        if (data.success) {
            Swal.fire({ icon: 'success', title: 'Now Serving!', timer: 1500, showConfirmButton: false })
                .then(() => location.reload());
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: data.message });
        }
    });
}

function completeQueue(id) {
    Swal.fire({
        title: 'Complete Service?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        confirmButtonText: '<i class="fas fa-check me-1"></i> Complete'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/staff/queue/${id}/complete`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken }
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Service Completed!', timer: 1500, showConfirmButton: false })
                        .then(() => location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message });
                }
            });
        }
    });
}

function skipQueue(id) {
    Swal.fire({
        title: 'Skip Patient?',
        text: 'The patient will be moved to skipped queue.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-forward me-1"></i> Skip'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/staff/queue/${id}/skip`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken }
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Patient Skipped', timer: 1500, showConfirmButton: false })
                        .then(() => location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message });
                }
            });
        }
    });
}

function cancelQueue(id) {
    Swal.fire({
        title: 'Cancel Queue?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: '<i class="fas fa-times me-1"></i> Cancel Queue'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/staff/queue/${id}/cancel`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken }
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Queue Cancelled', timer: 1500, showConfirmButton: false })
                        .then(() => location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message });
                }
            });
        }
    });
}

function viewDetails(id) {
    // Find queue data from the page
    const queues = @json($recentQueues);
    const queue = queues.data ? queues.data.find(q => q.id === id) : queues.find(q => q.id === id);
    
    if (!queue) {
        Swal.fire({ icon: 'error', title: 'Queue not found' });
        return;
    }
    
    const statusColors = {
        'waiting': '#ffc107',
        'called': '#0dcaf0',
        'serving': '#0d6efd',
        'completed': '#198754',
        'skipped': '#6c757d',
        'cancelled': '#dc3545'
    };
    
    const html = `
        <div class="text-start" style="font-size: 0.9rem;">
            <div class="text-center mb-3">
                <span class="badge fs-5 px-4 py-2" style="background: ${statusColors[queue.status] || '#6c757d'}">
                    ${queue.queue_number}
                </span>
            </div>
            <table class="table table-sm table-borderless mb-0">
                <tr>
                    <td class="text-muted" style="width: 40%;">Patient</td>
                    <td class="fw-bold">${queue.patient?.full_name || queue.patient?.first_name + ' ' + queue.patient?.last_name || 'Guest'}</td>
                </tr>
                <tr>
                    <td class="text-muted">Service</td>
                    <td><span class="badge" style="background: ${queue.service?.color || '#6c757d'}">${queue.service?.name || 'N/A'}</span></td>
                </tr>
                <tr>
                    <td class="text-muted">Priority</td>
                    <td>${queue.priority?.name || 'Regular'}</td>
                </tr>
                <tr>
                    <td class="text-muted">Type</td>
                    <td><span class="badge ${queue.queue_type === 'online' ? 'bg-primary' : 'bg-success'}">${queue.queue_type === 'online' ? 'Online' : 'Walk-in'}</span></td>
                </tr>
                <tr>
                    <td class="text-muted">Status</td>
                    <td><span class="badge" style="background: ${statusColors[queue.status]}">${queue.status.charAt(0).toUpperCase() + queue.status.slice(1)}</span></td>
                </tr>
                <tr>
                    <td class="text-muted">Joined</td>
                    <td>${new Date(queue.created_at).toLocaleString()}</td>
                </tr>
                ${queue.called_at ? `<tr><td class="text-muted">Called</td><td>${new Date(queue.called_at).toLocaleString()}</td></tr>` : ''}
                ${queue.served_at ? `<tr><td class="text-muted">Served</td><td>${new Date(queue.served_at).toLocaleString()}</td></tr>` : ''}
                ${queue.completed_at ? `<tr><td class="text-muted">Completed</td><td>${new Date(queue.completed_at).toLocaleString()}</td></tr>` : ''}
                ${queue.counter ? `<tr><td class="text-muted">Counter</td><td>${queue.counter.name}</td></tr>` : ''}
            </table>
        </div>
    `;
    
    Swal.fire({
        title: 'Queue Details',
        html: html,
        width: 450,
        showCloseButton: true,
        showConfirmButton: false
    });
}
</script>
@endpush
