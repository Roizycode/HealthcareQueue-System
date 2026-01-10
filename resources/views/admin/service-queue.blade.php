@extends('layouts.admin')

@section('title', 'Service Queue - Smart Healthcare')

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">
            <span class="rounded-circle d-inline-block me-2" style="width: 12px; height: 12px; background: {{ $service->color ?? '#0d6efd' }};"></span>
            {{ $service->name ?? 'Service' }} Queue
        </h4>
        <p class="text-muted small mb-0">Manage queue for this service</p>
    </div>
    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i> Back
    </a>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <small class="text-muted text-uppercase d-block mb-1" style="font-size: 0.65rem;">Waiting</small>
                <h5 class="fw-bold text-warning mb-0">{{ $stats['waiting'] ?? 0 }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <small class="text-muted text-uppercase d-block mb-1" style="font-size: 0.65rem;">Serving</small>
                <h5 class="fw-bold text-primary mb-0">{{ $stats['serving'] ?? 0 }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <small class="text-muted text-uppercase d-block mb-1" style="font-size: 0.65rem;">Completed</small>
                <h5 class="fw-bold text-success mb-0">{{ $stats['completed'] ?? 0 }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <small class="text-muted text-uppercase d-block mb-1" style="font-size: 0.65rem;">Avg Wait</small>
                <h5 class="fw-bold text-info mb-0">{{ $stats['avg_wait'] ?? 0 }}m</h5>
            </div>
        </div>
    </div>
</div>

<!-- Queue Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3">
        <h6 class="fw-bold mb-0">Queue List</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center" style="font-size: 0.8rem;">
                <thead class="bg-light">
                    <tr>
                        <th class="border-bottom-0">Queue #</th>
                        <th class="border-bottom-0">Patient</th>
                        <th class="border-bottom-0">Priority</th>
                        <th class="border-bottom-0">Status</th>
                        <th class="border-bottom-0">Counter</th>
                        <th class="border-bottom-0">Wait Time</th>
                        <th class="border-bottom-0">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($waitingList ?? [] as $queue)
                    <tr>
                        <td class="fw-bold">{{ $queue->queue_number }}</td>
                        <td>{{ $queue->patient->full_name ?? 'Guest' }}</td>
                        <td>
                            <span class="badge text-white" style="background-color: {{ $queue->priority->color ?? '#6c757d' }};">
                                {{ $queue->priority->code ?? 'REG' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $queue->status === 'waiting' ? 'warning text-dark' : ($queue->status === 'serving' ? 'success' : 'primary') }}">
                                {{ ucfirst($queue->status) }}
                            </span>
                        </td>
                        <td class="text-muted">{{ $queue->counter->name ?? '-' }}</td>
                        <td class="text-muted" style="font-family: monospace;">
                            {{ $queue->created_at->diff(now())->format('%H:%I:%S') }}
                        </td>
                        <td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary" title="View Details" onclick='viewQueueDetails(@json($queue))'>
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button" class="btn btn-outline-warning" title="Edit Queue" onclick='editQueue(@json($queue))'>
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger" title="Delete Queue" onclick="deleteQueue({{ $queue->id }})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-2x mb-2 opacity-25"></i>
                            <p class="mb-0 small">No patients in queue</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const priorities = @json($priorities ?? []);

    function viewQueueDetails(queue) {
         let html = `
            <div class="text-start p-3 bg-light rounded mt-2">
                <p><strong>Patient:</strong> ${queue.patient.full_name}</p>
                <p><strong>Service:</strong> ${queue.service?.name || '-'}</p>
                <p><strong>Priority:</strong> ${queue.priority?.name || '-'}</p>
                <p><strong>Status:</strong> <span class="badge bg-secondary">${queue.status.toUpperCase()}</span></p>
                <p><strong>Time:</strong> ${new Date(queue.created_at).toLocaleTimeString()}</p>
            </div>
         `;
         Swal.fire({
            title: `Queue #${queue.queue_number}`,
            html: html,
            confirmButtonText: 'Close'
         });
    }

    function editQueue(queue) {
        let options = {};
        priorities.forEach(p => {
             options[p.id] = p.name;
        });

        Swal.fire({
            title: `Edit Queue #${queue.queue_number}`,
            text: 'Update Priority Level',
            input: 'select',
            inputOptions: options,
            inputValue: queue.priority_id,
            showCancelButton: true,
            confirmButtonText: 'Update',
            showLoaderOnConfirm: true,
            preConfirm: (value) => {
                return fetch(`/staff/queue/${queue.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.csrfToken
                    },
                    body: JSON.stringify({ priority_id: value })
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(json => Promise.reject(json.message || 'Error'));
                    }
                    return response.json();
                })
                .catch(error => {
                    Swal.showValidationMessage(`Request failed: ${error}`);
                });
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire('Updated!', 'Queue priority updated.', 'success').then(() => location.reload());
            }
        });
    }

    function deleteQueue(id) {
        Swal.fire({
            title: 'Delete Queue Item?',
            text: "This will remove the patient from the queue (Cancel).",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, remove it'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/staff/queue/${id}/cancel`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': window.csrfToken }
                })
                .then(r => r.json())
                .then(data => {
                    if(data.success) {
                        Swal.fire('Deleted!', 'Queue item has been removed.', 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                });
            }
        });
    }
</script>
@endpush
