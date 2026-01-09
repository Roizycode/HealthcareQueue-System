@extends('layouts.staff')

@section('title', 'Priority Queue - HealthQueue Staff')

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h4 class="fw-bold text-dark mb-1">Priority Queue</h4>
        <p class="text-muted small mb-0">Manage queues by priority level</p>
    </div>
</div>

<div class="row g-4">
    @foreach($priorities as $priority)
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom-0 pt-3 pb-2">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0" style="color: {{ $priority->color }};">
                        {{ $priority->name }}
                    </h6>
                    <span class="badge bg-light text-dark rounded-pill border" id="count-{{ $priority->id }}">0</span>
                </div>
            </div>
            <div class="card-body p-0" id="list-{{ $priority->id }}">
                <div class="text-center py-4 text-muted small">
                    <div class="spinner-border spinner-border-sm text-secondary mb-2" role="status"></div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    loadPriorityQueues();
    setInterval(loadPriorityQueues, 10000);
});

function loadPriorityQueues() {
    fetch('/staff/queue/waiting')
        .then(r => r.json())
        .then(data => {
            if (!data.success) return;
            
            // Group by priority
            const grouped = {};
            @foreach($priorities as $priority)
            grouped[{{ $priority->id }}] = [];
            @endforeach
            
            data.data.forEach(q => {
                const pId = getPriorityId(q.priority_code);
                if (grouped[pId]) grouped[pId].push(q);
            });
            
            // Render each priority list
            @foreach($priorities as $priority)
            renderList({{ $priority->id }}, grouped[{{ $priority->id }}] || []);
            @endforeach
        });
}

function getPriorityId(code) {
    const map = {
        @foreach($priorities as $priority)
        '{{ $priority->code }}': {{ $priority->id }},
        @endforeach
    };
    return map[code] || 0;
}

function renderList(priorityId, queues) {
    const container = document.getElementById('list-' + priorityId);
    const countEl = document.getElementById('count-' + priorityId);
    
    countEl.textContent = queues.length;
    
    if (queues.length === 0) {
        container.innerHTML = '<div class="text-center py-4 text-muted"><i class="fas fa-inbox fa-2x opacity-25 mb-2"></i><br><small>No patients</small></div>';
        return;
    }
    
    let html = '<ul class="list-group list-group-flush">';
    queues.forEach(q => {
        html += `
            <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                <div>
                    <strong>${q.queue_number}</strong><br>
                    <small class="text-muted">${q.patient_name}</small>
                </div>
                <button class="btn btn-sm btn-primary rounded-pill" onclick="callQueue(${q.id})">
                    <i class="fas fa-bell"></i>
                </button>
            </li>
        `;
    });
    html += '</ul>';
    container.innerHTML = html;
}

function callQueue(id) {
    Swal.fire({
        title: 'Call Patient',
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
            callPatientToCounter(id, 1);
        } else if (result.isDenied) {
            callPatientToCounter(id, 2);
        }
    });
}

function callPatientToCounter(id, counterId) {
    fetch(`/staff/queue/${id}/call`, {
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
                loadPriorityQueues();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Failed to call patient.',
                confirmButtonColor: '#dc3545'
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
