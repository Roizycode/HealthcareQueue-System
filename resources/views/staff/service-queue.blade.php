@extends('layouts.staff')

@section('title', 'Queue - HealthQueue Staff')

@section('content')
<div class="row g-4">
    <!-- LEFT: Serving & Stats -->
    <div class="col-lg-5 col-xl-4">
        <div class="mb-4">
            <h4 class="fw-bold text-dark mb-1">{{ $service->name }}</h4>
            <div class="d-flex align-items-center">
                </span>
                <p class="text-muted small mb-0">Monitor service performance</p>
            </div>
        </div>

        <!-- Serving Card -->
        <div class="card border-0 shadow-sm mb-4 bg-primary text-white overflow-hidden" style="background-color: {{ $service->color }} !important;">
            <div class="card-body p-4 text-center position-relative">
                 <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, transparent 100%); pointer-events: none;"></div>
                
                <p class="text-white-50 small text-uppercase letter-spacing-1 mb-2">Since {{ now()->format('H:i') }}</p>

                @if($serving->first())
                <h1 class="display-1 fw-bold mb-0">{{ $serving->first()->queue_number }}</h1>
                <div class="mt-3">
                    <span class="badge bg-white text-dark rounded-pill px-3 py-2 fw-medium">
                        {{ $serving->first()->patient->full_name ?? 'Guest' }}
                    </span>
                </div>
                @else
                <div class="py-4">
                    <h2 class="fw-bold mb-0 opacity-50">--</h2>
                    <p class="text-white-50 small">No patient serving</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Mini Stats -->
        <div class="card border-0 shadow-sm">
             <div class="card-body p-0">
                <div class="d-flex justify-content-between text-center divide-x">
                    <div class="p-3 flex-grow-1 border-end">
                        <h3 class="fw-bold text-dark mb-0">{{ $waiting->count() }}</h3>
                         <h6 class="text-muted small text-uppercase mb-0">Waiting</h6>
                    </div>
                    <div class="p-3 flex-grow-1 border-end">
                        <h3 class="fw-bold text-success mb-0">{{ $serving->count() }}</h3>
                         <h6 class="text-muted small text-uppercase mb-0">Serving</h6>
                    </div>
                    <div class="p-3 flex-grow-1">
                        <h3 class="fw-bold text-muted mb-0">{{ \App\Models\Queue::where('service_id', $service->id)->whereDate('created_at', today())->where('status', 'completed')->count() }}</h3>
                         <h6 class="text-muted small text-uppercase mb-0">Done</h6>
                    </div>
                </div>
             </div>
        </div>

        <!-- Last Served Card -->
        @if($lastServed = $completed->first())
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white py-3 border-0">
                <h6 class="fw-bold mb-0 text-uppercase text-muted small">Last Served</h6>
            </div>
            <div class="card-body pt-0">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Ticket:</span>
                    <span class="fw-bold">{{ $lastServed->queue_number }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Patient:</span>
                    <span class="text-end fw-medium">{{ $lastServed->patient->full_name }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Duration:</span>
                    <span>{{ $lastServed->service_duration ?? 0 }} mins</span>
                </div>
                <div class="d-flex justify-content-between mb-0">
                    <span class="text-muted">Completed:</span>
                    <span>{{ $lastServed->completed_at ? $lastServed->completed_at->format('H:i') : '-' }}</span>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- RIGHT: Waiting List -->
    <div class="col-lg-7 col-xl-8">
        <div class="d-flex align-items-center mb-4 position-relative">
            <h5 class="fw-bold text-dark mb-0 position-absolute start-50 translate-middle-x">Waiting List</h5>
            <div class="d-flex gap-2 ms-auto">
                <button class="btn btn-primary btn-sm rounded" onclick="callNextPatient()">
                    <i class="fas fa-bell me-1"></i> Call Next
                </button>
                <button class="btn btn-outline-secondary btn-sm rounded" onclick="location.reload()">
                    <i class="fas fa-sync-alt me-1"></i> Refresh
                </button>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-center border-bottom-0">Ticket</th>
                                <th class="text-center border-bottom-0">Patient</th>
                                <th class="text-center border-bottom-0">Priority</th>
                                <th class="text-center border-bottom-0">Wait</th>
                                <th class="text-center border-bottom-0">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($waiting as $queue)
                            <tr>
                                <td class="text-center fw-bold">{{ $queue->queue_number }}</td>
                                <td class="text-center">
                                    <div class="fw-medium">{{ $queue->patient->full_name ?? 'Guest' }}</div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border">{{ $queue->priority->name }}</span>
                                </td>
                                <td class="text-center text-muted small">{{ $queue->created_at->format('h:iA') }}</td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary" onclick="callPatient({{ $queue->id }})">
                                        Call
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <p class="mb-0">No patients waiting for this service.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Completed List -->
        <h5 class="fw-bold text-dark mb-3 mt-5 text-center">Recent Completed</h5>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-center border-bottom-0">Ticket</th>
                                <th class="text-center border-bottom-0">Patient</th>
                                <th class="text-center border-bottom-0">Duration</th>
                                <th class="text-center border-bottom-0">Completed</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($completed as $c)
                            <tr>
                                <td class="text-center fw-bold text-muted">{{ $c->queue_number }}</td>
                                <td class="text-center">{{ $c->patient->full_name }}</td>
                                <td class="text-center">{{ $c->service_duration ?? 0 }} mins</td>
                                <td class="text-center">{{ $c->completed_at ? $c->completed_at->format('h:iA') : '-' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center py-4 text-muted">No completed services yet.</td></tr>
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
window.csrfToken = '{{ csrf_token() }}';
let selectedQueueId = null;

function callPatient(id) {
    selectedQueueId = id;
    showCounterModal();
}

function callNextPatient() {
    selectedQueueId = null;
    showCounterModal();
}

function showCounterModal() {
    Swal.fire({
        title: selectedQueueId ? 'Call Patient' : 'Call Next Patient',
        text: 'Select counter to call patient',
        icon: 'question',
        showCancelButton: true,
        showDenyButton: true,
        confirmButtonText: 'Counter 1',
        denyButtonText: 'Counter 2',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#0d6efd',
        denyButtonColor: '#6c757d',
        cancelButtonColor: '#dc3545',
    }).then((result) => {
        if (result.isConfirmed) {
            selectCounter(1);
        } else if (result.isDenied) {
            selectCounter(2);
        }
    });
}

function selectCounter(counterId) {
    const url = selectedQueueId 
        ? `/staff/queue/${selectedQueueId}/call` 
        : `/staff/service/{{ $service->id }}/call-next`;
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.csrfToken
        },
        body: JSON.stringify({ counter_id: counterId })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Patient Called!',
                text: data.message || 'Patient has been called to counter.',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'info',
                title: 'No Patients Available',
                text: data.message || 'There are currently no patients waiting for this service.',
                confirmButtonColor: '#0d6efd'
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to call patient. Please try again.',
            confirmButtonColor: '#dc3545'
        });
    });
}
</script>
@endpush
