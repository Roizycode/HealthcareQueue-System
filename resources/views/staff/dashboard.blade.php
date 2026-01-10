@extends('layouts.staff')

@section('title', 'Staff Dashboard - Smart Healthcare')

@section('content')
<div class="row g-4">
    <!-- LEFT COLUMN: Active Queue & Actions -->
    <div class="col-lg-5 col-xl-4">
        <!-- Header / Status -->
        <div class="mb-4">
            <h4 class="fw-bold text-dark">Dashboard</h4>
            <p class="text-muted small">
                Welcome, {{ auth()->user()->name ?? 'Staff' }} &bull; {{ now()->format('l, F j') }}
            </p>
        </div>

        <!-- Now Serving Card -->
        <div class="card border-0 shadow-sm mb-4 bg-primary text-white overflow-hidden">
            <div class="card-body p-4 text-center position-relative">
                <!-- Background decoration -->
                <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, transparent 100%); pointer-events: none;"></div>
                
                <p class="text-white-50 small text-uppercase letter-spacing-1 mb-2">Now Serving</p>
                
                <div id="nowServingContent">
                @if($currentServing)
                    <h1 class="display-1 fw-bold mb-0">{{ $currentServing->queue_number }}</h1>
                    <div class="mt-3">
                        <span class="badge bg-white text-primary rounded-pill px-3 py-2 fw-medium">
                            <i class="fas fa-user me-1"></i> {{ $currentServing->patient->full_name }}
                        </span>
                    </div>
                    <div class="mt-2 text-white-50 small">
                        @php
                            $mins = $currentServing->serving_started_at ? floor($currentServing->serving_started_at->diffInMinutes()) : 0;
                        @endphp
                        Running for {{ $mins == 0 ? 'Just now' : ($mins == 1 ? '1 min' : "$mins mins") }}
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2 mt-4">
                        @if($currentServing->serving_started_at)
                            <!-- Service has started - show Complete -->
                            <button class="btn btn-success fw-bold py-3" onclick="completeQueue({{ $currentServing->id }})">
                                <i class="fas fa-check-circle me-2"></i> Complete Service
                            </button>
                        @else
                            <!-- Service not started - show Start Service -->
                            <button class="btn btn-warning fw-bold py-3 text-dark" onclick="startService({{ $currentServing->id }})">
                                <i class="fas fa-play-circle me-2"></i> Start Service
                            </button>
                        @endif
                        
                        <div class="row g-2">
                            <div class="col-6">
                                <button class="btn btn-outline-light w-100" onclick="skipQueue({{ $currentServing->id }})">
                                    <i class="fas fa-forward me-2"></i> Skip
                                </button>
                            </div>
                            <div class="col-6">
                                <button class="btn btn-outline-light w-100" onclick="recallQueue({{ $currentServing->id }})">
                                    <i class="fas fa-bullhorn me-2"></i> Recall
                                </button>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="py-4">
                        <i class="fas fa-coffee fa-3x text-white-50 mb-3"></i>
                        <h5>No Active Patient</h5>
                        <p class="text-white-50 small">Ready for the next patient?</p>
                        <button class="btn btn-light fw-bold w-100 mt-3" onclick="callNext()">
                            <i class="fas fa-bell me-2"></i> Call Next Patient
                        </button>
                    </div>
                @endif
                </div>
            </div>
        </div>

        <!-- Quick Stats Information -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="d-flex justify-content-between text-center divide-x">
                    <div class="p-3 flex-grow-1 border-end">
                        <h6 class="text-muted small text-uppercase mb-1">Waiting</h6>
                        <h3 class="fw-bold text-dark mb-0" id="statWaiting">-</h3>
                    </div>
                    <div class="p-3 flex-grow-1 border-end">
                        <h6 class="text-muted small text-uppercase mb-1">Served</h6>
                        <h3 class="fw-bold text-success mb-0" id="statCompleted">-</h3>
                    </div>
                    <div class="p-3 flex-grow-1">
                        <h6 class="text-muted small text-uppercase mb-1">Skipped</h6>
                        <h3 class="fw-bold text-danger mb-0" id="statSkipped">-</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- RIGHT COLUMN: Waiting List -->
    <div class="col-lg-7 col-xl-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="fw-bold mb-0">Waiting Room</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('display') }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-external-link-alt me-1"></i> Display
                </a>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#walkinModal">
                    <i class="fas fa-plus me-1"></i> Add Walk-in
                </button>
            </div>
        </div>

        <!-- Filters (Clean Tabs) -->
        <div class="mb-3" x-data="{ activeService: 'all' }">
            <ul class="nav nav-pills small gap-2">
                <li class="nav-item">
                    <a href="#" class="nav-link border" :class="{ 'active': activeService === 'all' }" 
                       @click.prevent="activeService = 'all'; filterQueue('all')">All</a>
                </li>
                @foreach($services as $service)
                <li class="nav-item">
                    <a href="#" class="nav-link border" :class="{ 'active': activeService === '{{ $service->id }}' }"
                       @click.prevent="activeService = '{{ $service->id }}'; filterQueue('{{ $service->id }}')">
                       <span class="d-inline-block rounded-circle me-1" style="width: 8px; height: 8px; background: {{ $service->color }};"></span>
                       {{ $service->name }}
                    </a>
                </li>
                @endforeach
            </ul>
        </div>

        <!-- Waiting List Table -->
        <div class="card border-0 shadow-sm" x-data="{ currentTab: 'waiting' }">
            <div class="card-header bg-white border-0 pt-3 pb-0">
                <ul class="nav nav-tabs card-header-tabs">
                    <li class="nav-item">
                        <a class="nav-link fw-bold" :class="{ 'active': currentTab === 'waiting' }" href="#" 
                           @click.prevent="currentTab = 'waiting'; setCurrentTab('waiting')">Waiting</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-bold" :class="{ 'active': currentTab === 'serving' }" href="#" 
                           @click.prevent="currentTab = 'serving'; setCurrentTab('serving'); loadServing()">Serving</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-bold" :class="{ 'active': currentTab === 'completed' }" href="#" 
                           @click.prevent="currentTab = 'completed'; setCurrentTab('completed'); loadCompleted()">Recent Completed</a>
                    </li>
                </ul>
            </div>
            <div class="card-body p-0">
                <div class="tab-content">
                    <!-- Waiting List -->
                    <div class="tab-pane fade" :class="{ 'show active': currentTab === 'waiting' }">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm align-middle mb-0" style="font-size: 0.85rem;">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="text-center border-bottom-0">Ticket</th>
                                        <th class="text-center border-bottom-0">Patient Name</th>
                                        <th class="text-center border-bottom-0">Service</th>
                                        <th class="text-center border-bottom-0">Priority</th>
                                        <th class="text-center border-bottom-0">Joined Time</th>
                                        <th class="text-center border-bottom-0">Estimated Wait</th>
                                        <th class="text-center border-bottom-0">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="queueList">
                                    <tr><td colspan="7" class="text-center py-5 text-muted">Loading...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Serving List -->
                    <div class="tab-pane fade" :class="{ 'show active': currentTab === 'serving' }">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm align-middle mb-0" style="font-size: 0.85rem;">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="text-center border-bottom-0">Ticket</th>
                                        <th class="text-center border-bottom-0">Patient Name</th>
                                        <th class="text-center border-bottom-0">Service</th>
                                        <th class="text-center border-bottom-0">Counter</th>
                                        <th class="text-center border-bottom-0">Start Time</th>
                                        <th class="text-center border-bottom-0">Elapsed Time</th>
                                        <th class="text-center border-bottom-0">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="servingList">
                                    <tr><td colspan="5" class="text-center py-5 text-muted">Loading...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Completed List -->
                    <div class="tab-pane fade" :class="{ 'show active': currentTab === 'completed' }">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm align-middle mb-0" style="font-size: 0.85rem;">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="text-center border-bottom-0">Ticket</th>
                                        <th class="text-center border-bottom-0">Patient Name</th>
                                        <th class="text-center border-bottom-0">Service</th>
                                        <th class="text-center border-bottom-0">Counter</th>
                                        <th class="text-center border-bottom-0">Duration</th>
                                        <th class="text-center border-bottom-0">Completed Time</th>
                                        <th class="text-center border-bottom-0">Payment Status</th>
                                    </tr>
                                </thead>
                                <tbody id="completedList">
                                    <tr><td colspan="5" class="text-center py-5 text-muted">Loading...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('modals')
<!-- Counter Selection Modal -->
<div class="modal fade" id="counterModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold">Select Counter</h6>
                <button type="button" class="btn-close small" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-grid gap-2">
                    @foreach($counters as $counter)
                        @if($counter->status === 'open')
                            <button class="btn btn-outline-primary text-start" onclick="selectCounter({{ $counter->id }})">
                                <span class="d-block fw-bold">{{ $counter->name }}</span>
                                <small class="text-muted">{{ $counter->service->name ?? '' }}</small>
                            </button>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>





<!-- Walk-in Registration Modal (Cleaned up) -->
<div class="modal fade" id="walkinModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold">Quick Registration</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('staff.patients.register') }}" method="POST" id="quickRegisterForm" onsubmit="event.preventDefault(); submitQuickRegister()">
                    @csrf
                    <input type="hidden" name="queue_type" value="walk_in">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label small text-muted">First Name</label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted">Last Name</label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-muted">Phone</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">+63</span>
                                <input type="tel" inputmode="numeric" id="quick_phone_display" class="form-control border-start-0 ps-0" placeholder="9xxxxxxxxx" maxlength="10" minlength="10" pattern="9[0-9]{9}" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);" title="10-digit mobile number starting with 9" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-muted">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" placeholder="patient@example.com" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-muted">Service</label>
                            <select name="service_id" class="form-select" required>
                                <option value="">Select...</option>
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}">{{ $service->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-muted">Priority</label>
                            <select name="priority_id" class="form-select" required>
                                @foreach($priorities as $priority)
                                    <option value="{{ $priority->id }}" {{ $priority->code === 'REG' ? 'selected' : '' }}>
                                        {{ $priority->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 pt-2">
                            <button type="submit" class="btn btn-primary w-100">Register</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endpush

<!-- Toast -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
    <div id="notificationToast" class="toast border-0 shadow" role="alert">
        <div class="d-flex align-items-center">
            <div class="toast-body fw-medium" id="toastMessage"></div>
            <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>
</div>
@endsection

@push('styles')
<style>
    /* Prevent SweetAlert animation shaking */
    .swal2-no-animation {
        animation: none !important;
    }
    
    .swal2-no-backdrop-animation {
        animation: none !important;
    }
    
    .swal2-container {
        padding: 0 !important;
    }
    
    .swal2-show {
        animation: none !important;
        transform: none !important;
    }
    
    .swal2-backdrop-show {
        animation: none !important;
    }
</style>
@endpush

@push('scripts')
<script>
let currentServiceFilter = 'all';
let selectedQueueId = null;
let skipQueueId = null; // Can be removed if not used by modal anymore
let currentTab = 'waiting';

// Helper function to format time properly
function formatTime(minutes) {
    const m = Math.floor(minutes);
    if (m === 0) return 'Just now';
    if (m === 1) return '1 min';
    if (m < 60) return `${m} mins`;
    const hours = Math.floor(m / 60);
    const mins = m % 60;
    if (mins === 0) return `${hours}h`;
    return `${hours}h ${mins}m`;
}

// Helper function to format wait time
function formatWaitTime(minutes) {
    const m = Math.floor(minutes);
    if (m < 1) return 'Just now';
    if (m === 1) return '1 min';
    if (m < 60) return `${m} mins`;
    const hours = Math.floor(m / 60);
    const mins = m % 60;
    return mins === 0 ? `${hours}h` : `${hours}h ${mins}m`;
}

document.addEventListener('DOMContentLoaded', function() {
    loadQueue();
    loadStats();
    loadCurrentServing();
    
    setInterval(() => {
        if(currentTab === 'waiting') loadQueue();
        else if(currentTab === 'serving') loadServing();
        else loadCompleted();
        
        loadStats();
        loadCurrentServing();
    }, 5000);
});

function setCurrentTab(tab) {
    currentTab = tab;
}

function loadCurrentServing() {
    fetch('/staff/queue/current').then(r => r.json()).then(data => {
        if (!data.success) return;
        
        const container = document.getElementById('nowServingContent');
        const d = data.data;

        if (d) {
            // Has active patient
            // Only update if content changed to avoid flicker/resetting state if user interacting? 
            // Ideally check ID but simple innerHTML replacement is acceptable here.
            
            // Check if we are already displaying this queue to avoid re-render flicker
            // Assuming we aren't doing heavy DOM manipulation on it
            
            container.innerHTML = `
                <h1 class="display-1 fw-bold mb-0">${d.queue_number}</h1>
                <div class="mt-3">
                    <span class="badge bg-white text-primary rounded-pill px-3 py-2 fw-medium">
                        <i class="fas fa-user me-1"></i> ${d.patient_name}
                    </span>
                </div>
                <div class="mt-2 text-white-50 small">
                    Running for ${formatTime(d.duration)}
                </div>
                <div class="d-grid gap-2 mt-4">
                    ${d.serving_started_at ? `
                        <button class="btn btn-success fw-bold py-3" onclick="completeQueue(${d.id})">
                            <i class="fas fa-check-circle me-2"></i> Complete Service
                        </button>
                    ` : `
                        <button class="btn btn-warning fw-bold py-3 text-dark" onclick="startService(${d.id})">
                            <i class="fas fa-play-circle me-2"></i> Start Service
                        </button>
                    `}
                    <div class="row g-2">
                        <div class="col-6">
                            <button class="btn btn-outline-light w-100" onclick="skipQueue(${d.id})">
                                <i class="fas fa-forward me-2"></i> Skip
                            </button>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-outline-light w-100" onclick="recallQueue(${d.id})">
                                <i class="fas fa-bullhorn me-2"></i> Recall
                            </button>
                        </div>
                    </div>
                </div>
            `;
        } else {
            // No active patient
            // No active patient
             container.innerHTML = `
                <div class="py-4">
                    <i class="fas fa-coffee fa-3x text-white opacity-75 mb-3"></i>
                    <h5 class="text-white">No Active Patient</h5>
                    <p class="text-white opacity-75 small">Ready for the next patient?</p>
                    <button class="btn btn-light fw-bold w-100 mt-3" onclick="callNext()">
                        <i class="fas fa-bell me-2"></i> Call Next Patient
                    </button>
                </div>
            `;
        }
    });
}


function filterQueue(serviceId) {
    currentServiceFilter = serviceId;
    if(currentTab === 'waiting') loadQueue();
    else if(currentTab === 'serving') loadServing();
    else loadCompleted();
    loadStats();
}

function loadQueue() {
    const url = currentServiceFilter === 'all' 
        ? '/staff/queue/waiting' 
        : `/staff/queue/waiting?service_id=${currentServiceFilter}`;
    
    fetch(url).then(r => r.json()).then(data => {
        if (data.success) renderQueue(data.data);
    });
}

function loadStats() {
    const url = currentServiceFilter === 'all' 
        ? '/staff/queue/stats' 
        : `/staff/queue/stats?service_id=${currentServiceFilter}`;
    
    fetch(url).then(r => r.json()).then(data => {
        if (data.success) {
            document.getElementById('statWaiting').textContent = data.data.waiting;
            document.getElementById('statCompleted').textContent = data.data.completed;
            document.getElementById('statSkipped').textContent = data.data.skipped;
        }
    });
}

function renderQueue(queues) {
    const container = document.getElementById('queueList');
    if (queues.length === 0) {
        container.innerHTML = '<tr><td colspan="7" class="text-center py-5 text-muted"><i class="fas fa-inbox fa-2x opacity-25 mb-2"></i><br>Queue is empty</td></tr>';
        return;
    }
    
    let html = '';
    queues.forEach((q) => {
        html += `
            <tr class="${q.priority_code === 'EMG' ? 'table-danger' : ''}">
                <td class="fw-bold text-dark text-center">${q.queue_number}</td>
                <td class="text-center">
                    <div class="fw-medium">${q.patient_name}</div>
                    <div class="text-muted small" style="font-size: 0.75rem;">${q.patient_phone || ''}</div>
                </td>
                <td class="text-center"><span class="badge bg-light text-dark border">${q.service}</span></td>
                <td class="text-center">
                    <span class="badge ${
                        q.priority_code === 'EMG' ? 'bg-danger text-white' : 
                        q.priority_code === 'SNR' ? 'bg-purple text-white' : 
                        q.priority_code === 'PWD' ? 'bg-info text-dark' : 
                        'bg-light text-dark border'
                    }">${q.priority_name || 'Regular'}</span>
                </td>
                <td class="text-center text-muted small">${q.joined_time || '-'}</td>
                <td class="text-center text-muted small">${q.wait_time !== '-' ? formatWaitTime(q.wait_time) : 'Just now'}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-primary rounded" onclick="callQueue(${q.id})">Call</button>
                </td>
            </tr>
        `;
    });
    container.innerHTML = html;
}

function getModal(id) {
    const el = document.getElementById(id);
    return bootstrap.Modal.getOrCreateInstance(el);
}

function callNext() { selectedQueueId = null; getModal('counterModal').show(); }
function callQueue(id) { selectedQueueId = id; getModal('counterModal').show(); }

function startService(id) {
    Swal.fire({
        title: 'Start Service?',
        text: "Mark that you have started serving this patient.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Start Service',
        showLoaderOnConfirm: true,
        backdrop: true,
        preConfirm: () => {
             return fetch(`/staff/queue/${id}/start`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(response.statusText)
                }
                return response.json()
            })
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || "Failed");
                }
                return data;
            })
            .catch(error => {
                Swal.showValidationMessage(`${error}`)
            })
        }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Service Started!',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false,
                        backdrop: true
                    });
                    // Refresh data instead of reload
                    loadCurrentServing();
                    loadStats();
                    if(currentTab === 'waiting') loadQueue();
                    else if(currentTab === 'serving') loadServing();
                }
            }); 
        }

        function completeQueue(id) {
            Swal.fire({
                title: 'Complete Patient?',
                text: "This will mark the patient as served.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Complete',
                showLoaderOnConfirm: true,
                backdrop: true,
                allowOutsideClick: () => !Swal.isLoading(),
                preConfirm: () => {
                     return fetch(`/staff/queue/${id}/complete`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': window.csrfToken
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(response.statusText)
                        }
                        return response.json()
                    })
                    .then(data => {
                        if (!data.success) {
                            throw new Error(data.message || "Failed");
                        }
                        return data;
                    })
                    .catch(error => {
                        Swal.showValidationMessage(
                            `${error}`
                        )
                    })
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Completed!',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false,
                        backdrop: true
                    });
                    // Refresh data instead of reload
                    loadCurrentServing();
                    loadStats();
                    if(currentTab === 'completed') loadCompleted();
                    else if(currentTab === 'serving') loadServing();
                }
            }); 
        }

        function skipQueue(id) {
            Swal.fire({
                title: 'Skip Patient?',
                text: "Patient will be moved to skipped list.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#f8f9fa',
                cancelButtonText: '<span class="text-dark">Cancel</span>',
                confirmButtonText: 'Yes, Skip',
                showLoaderOnConfirm: true,
                backdrop: true,
                preConfirm: () => {
                     return fetch(`/staff/queue/${id}/skip`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': window.csrfToken
                        }
                    })
                    .then(response => {
                        if (!response.ok) throw new Error(response.statusText);
                        return response.json();
                    })
                    .then(data => {
                        if (!data.success) throw new Error(data.message || "Failed");
                        return data;
                    })
                    .catch(error => {
                        Swal.showValidationMessage(`${error}`);
                    })
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Skipped',
                        text: 'Patient marked as skipped.',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false,
                        backdrop: true
                    });
                    // Refresh data instead of reload
                    loadCurrentServing();
                    loadStats();
                    if(currentTab === 'waiting') loadQueue();
                }
            }); 
        }

// Function to play recall notification with voice
function playRecallNotification(queueNumber) {
    // Play a bell/ding sound first
    const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEARKwAAIhYAQACABAAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZiTYIC1+56+efTRAMUKfj8LZjHAU7k9r0yHMpBSl+zPLaizsKElyx6OyrWBUIQ5zd8sFuJAUuhM/z2Ik3CAlfuevnnk0QDFCn4/C2YxwFO5Pa9MhzKQUpfsz12os7ChJcsdzsq1gVCEOc3fLBbiQFL4TP89iJNwgJX7nr559NEAxQp+PwtmMcBTuT2vTIcykFKX7M9dqLOwgRXLDd7KtYFQhDnN3ywW4kBS+Ez/PYiTcICV+56+efTRAMUKfj8LZjHAU7k9r0yHMpBSl+zPXaizsIEVyw3eyrWBUIQ5zd8sFuJAUvhM/z2Ik3CAlfuevnn00QDFCn4/C2YxwFO5Pa9MhzKQUpfsz12os7CBFcsN3sq1gVCEOc3fLBbiQFL4TP89iJNwgJX7nr559NEAxQp+PwtmMcBTuT2vTIcykFKX7M9dqLOwgRXLDd7KtYFQhDnN3ywW4kBS+Ez/PYiTcICV+56+efTRAMUKfj8LZjHAU7k9r0yHMpBSl+zPXaizsIEVyw3eyrWBUIQ5zd8sFuJAUvhM/z2Ik3CAlfuevnn00QDFCn4/C2YxwFO5Pa9MhzKQUpfsz12os7CBFcsN3sq1gVCEOc3fLBbiQFL4TP89iJNwgJX7nr559NEAxQp+PwtmMcBQ==');
    
    audio.play().catch(e => console.log('Audio play failed:', e));
    
    // Use text-to-speech to announce the queue number
    if ('speechSynthesis' in window) {
        const utterance = new SpeechSynthesisUtterance(`Recalling ${queueNumber}. Please proceed to the counter.`);
        utterance.lang = 'en-US';
        utterance.rate = 0.9;
        utterance.pitch = 1.0;
        utterance.volume = 1.0;
        
        // Stop any ongoing speech and speak
        window.speechSynthesis.cancel();
        window.speechSynthesis.speak(utterance);
    }
}

function recallQueue(id) {
    fetch(`/staff/queue/${id}/recall`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.csrfToken
        }
    })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            playRecallNotification(data.data?.queue_number || 'Patient');
            
             Swal.fire({
                icon: 'success',
                title: 'Recalled!',
                text: data.message || 'Patient recalled successfully.',
                timer: 1500,
                showConfirmButton: false,
                backdrop: true
             });
             // Refresh data
             loadCurrentServing(); // In case we are recalling the current active patient
             if(currentTab === 'serving') loadServing();
        } else {
             Swal.fire({
                icon: 'error',
                title: 'Recall Failed',
                text: data.message
             });
        }
    })
    .catch(e => console.error(e));
}

function selectCounter(counterId) {
    const url = selectedQueueId ? `/staff/queue/${selectedQueueId}/call` : `/staff/queue/call-next`;
    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.csrfToken },
        body: JSON.stringify({ counter_id: counterId })
    })
    .then(r => r.json())
    .then(data => {
        getModal('counterModal').hide();
        
        if (data.success) {
            // Updated to avoid reload
            Swal.fire({
                icon: 'success',
                title: 'Calling Patient',
                text: `Calling ${data.data.queue_number}`,
                timer: 1500,
                showConfirmButton: false,
                backdrop: true
            });
            
            // Refresh dashboard
            loadCurrentServing();
            loadStats();
            if(currentTab === 'waiting') loadQueue();
            else if(currentTab === 'serving') loadServing();
            
        } else {
            Swal.fire({
                icon: 'info',
                title: 'No Patients Available',
                text: data.message || 'There are currently no patients waiting in the queue.',
                confirmButtonColor: '#0d6efd'
            });
        }
    })
    .catch(error => {
        getModal('counterModal').hide();
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to call next patient. Please try again.',
            confirmButtonColor: '#dc3545'
        });
    });
}


function loadCompleted() {
    const url = currentServiceFilter === 'all' 
        ? '/staff/queue/completed' 
        : `/staff/queue/completed?service_id=${currentServiceFilter}`;

    fetch(url).then(r => r.json()).then(data => {
        if(data.success) renderCompleted(data.data);
    });
}

function renderCompleted(data) {
     const list = document.getElementById('completedList');
     if(!list) return;
     
     if(!data || data.length === 0) {
         list.innerHTML = '<tr><td colspan="7" class="text-center py-5 text-muted">No recently completed patients</td></tr>';
         return;
     }
     list.innerHTML = data.map(q => `
        <tr>
            <td class="text-center fw-bold text-success">${q.queue_number}</td>
            <td class="text-center"><div class="fw-medium small">${q.patient_name || 'Guest'}</div></td>
            <td class="text-center"><small class="fw-bold text-muted">${q.service_name}</small></td>
            <td class="text-center small">${q.counter || '-'}</td>
            <td class="text-center small">${q.duration !== '-' ? formatTime(q.duration) : '-'}</td>
            <td class="text-center small">${q.completed_at}</td>
            <td class="text-center">
                ${q.payment_status === 'pending' 
                    ? '<span class="badge bg-warning text-dark">UNPAID</span>' 
                    : '<span class="badge bg-success">PAID</span>'}
            </td>
        </tr>
     `).join('');
}

function loadServing() {
    const url = currentServiceFilter === 'all' 
        ? '/staff/serving' 
        : `/staff/serving?service_id=${currentServiceFilter}`;

    fetch(url).then(r => r.json()).then(data => {
        if(data.success) renderServing(data.data);
    });
}

function renderServing(data) {
     const list = document.getElementById('servingList');
     if(!list) return;
     
     if(!data || data.length === 0) {
         list.innerHTML = '<tr><td colspan="7" class="text-center py-5 text-muted">No active patients</td></tr>';
         return;
     }
     list.innerHTML = data.map(q => {
        let startTimeDisplay = '-';
        if (q.status === 'called') {
            startTimeDisplay = '<span class="badge bg-warning text-dark">Calling</span>';
        } else if (q.serving_started_at) {
            startTimeDisplay = q.serving_started_at;
        }

        return `
        <tr>
            <td class="text-center fw-bold text-success">${q.queue_number}</td>
            <td class="text-center"><div class="fw-medium small">${q.patient_name || 'Guest'}</div></td>
            <td class="text-center"><small class="fw-bold text-muted">${q.service || '-'}</small></td> 
            <td class="text-center small">${q.counter || '-'}</td>
            <td class="text-center small">${startTimeDisplay}</td>
            <td class="text-center small fw-bold text-primary">${q.service_duration > 0 ? formatTime(q.service_duration) : '-'}</td>
            <td class="text-center">
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-success" onclick="completeQueue(${q.id})" title="Complete">
                        <i class="fas fa-check"></i>
                    </button>
                    <button class="btn btn-outline-warning" onclick="recallQueue(${q.id})" title="Recall">
                        <i class="fas fa-bullhorn"></i>
                    </button>
                </div>
            </td>
        </tr>
     `}).join('');
}
</script>
@endpush
