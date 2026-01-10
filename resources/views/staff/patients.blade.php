@extends('layouts.staff')

@section('title', 'All Patients - Smart Healthcare Staff')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Patients</h4>
        <p class="text-muted small mb-0">View and manage patient records</p>
    </div>
    <a href="{{ route('staff.patients.add') }}" class="btn btn-primary">
        <i class="fas fa-user-plus me-1"></i> Add Walk-in
    </a>
</div>

<!-- Search -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('staff.patients') }}" method="GET" class="row g-3">
            <div class="col-md-10">
                <input type="text" name="search" class="form-control" placeholder="Search by name, phone, or email..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary w-100">
                    <i class="fas fa-search me-1"></i> Search
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Patients Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 border-bottom-0">Patient</th>
                        <th class="border-bottom-0">Contact</th>
                        <th class="border-bottom-0 text-center">Category</th>
                        <th class="border-bottom-0 text-center">Previous Visits</th>
                        <th class="border-bottom-0 text-center">Last Visit</th>
                        <th class="text-end pe-4 border-bottom-0">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($patients as $patient)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3 text-secondary fw-bold border" style="width: 40px; height: 40px;">
                                    {{ substr($patient->first_name, 0, 1) }}{{ substr($patient->last_name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">
                                        {{ $patient->full_name }}
                                        @if($patient->queues_count > 0)
                                            <span class="badge bg-success bg-opacity-10 text-success ms-1" title="Returning Patient">
                                                <i class="fas fa-check-circle"></i>
                                            </span>
                                        @endif
                                    </div>
                                    <small class="text-muted">ID: {{ $patient->patient_id ?? $patient->id }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="small">{{ $patient->phone }}</div>
                            <div class="text-muted small">{{ $patient->email }}</div>
                        </td>
                        <td class="text-center">
                            @if($patient->is_senior)
                            <span class="badge bg-light text-dark border">Senior</span>
                            @elseif($patient->is_pwd)
                            <span class="badge bg-light text-dark border">PWD</span>
                            @else
                            <span class="badge bg-light text-muted border">Regular</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($patient->queues_count > 0)
                                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">
                                    <i class="fas fa-history me-1"></i>{{ $patient->queues_count }} visit{{ $patient->queues_count > 1 ? 's' : '' }}
                                </span>
                            @else
                                <span class="badge bg-light text-muted border">New Patient</span>
                            @endif
                        </td>
                        <td class="text-muted small text-center">
                            {{ $patient->queues->first() ? $patient->queues->first()->created_at->format('M d, Y') : '-' }}
                        </td>
                        <td class="text-end pe-4">
                            <button class="btn btn-sm btn-outline-secondary" onclick="viewPatient({{ json_encode($patient) }})">
                                <i class="fas fa-eye me-1"></i> View
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fas fa-users-slash mb-2"></i>
                            <p class="mb-0">No patients found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white border-top-0 py-3">
        {{ $patients->links() }}
    </div>
</div>

<!-- Add Patient Modal -->
<div class="modal fade" id="addPatientModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Add New Patient</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div id="addPatientError" class="alert alert-danger d-none mb-3 small"></div>
                <form action="{{ route('staff.patients.register') }}" method="POST" id="addPatientForm" onsubmit="event.preventDefault(); submitAddPatient();">
                    @csrf
                    <input type="hidden" name="queue_type" value="walk_in">
                    
                    <h6 class="fw-bold mb-3 text-uppercase text-muted small">Personal Information</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Phone Number <span class="text-danger">*</span></label>
                            <input type="tel" name="phone" class="form-control" required placeholder="+63">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Email Address</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                    </div>

                    <h6 class="fw-bold mb-3 text-uppercase text-muted small">Queue Details</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small">Service <span class="text-danger">*</span></label>
                            <select name="service_id" class="form-select" required>
                                <option value="">Select Service...</option>
                                @foreach($services as $service)
                                <option value="{{ $service->id }}">{{ $service->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Priority Level <span class="text-danger">*</span></label>
                            <select name="priority_id" class="form-select" required>
                                @foreach($priorities as $priority)
                                <option value="{{ $priority->id }}" {{ $priority->code === 'REG' ? 'selected' : '' }}>
                                    {{ $priority->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 mt-3">
                             <div class="d-flex gap-4">
                                <div class="form-check">
                                    <input type="checkbox" name="is_senior" class="form-check-input" id="isSenior">
                                    <label class="form-check-label small" for="isSenior">Senior Citizen</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="is_pwd" class="form-check-input" id="isPwd">
                                    <label class="form-check-label small" for="isPwd">Person with Disability (PWD)</label>
                                </div>
                             </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2 mt-4 pt-2">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary px-4" onclick="submitAddPatient()">Register Patient</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- View Patient Modal -->
<div class="modal fade" id="viewPatientModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Patient Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row">
                    <!-- Left Column: Patient Info -->
                    <div class="col-md-5 text-center border-end">
                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3 text-secondary fw-bold fs-3 border" style="width: 80px; height: 80px;" id="viewPatientInitials">
                            -
                        </div>
                        <h4 class="fw-bold mb-1" id="viewPatientName">-</h4>
                        <p class="text-muted small mb-2" id="viewPatientId">-</p>
                        <div id="viewPatientVisitBadge" class="mb-3"></div>

                        <div class="row g-3 text-start">
                            <div class="col-6">
                                <label class="small text-muted text-uppercase fw-bold">Phone</label>
                                <div class="fw-medium small" id="viewPatientPhone">-</div>
                            </div>
                            <div class="col-6">
                                <label class="small text-muted text-uppercase fw-bold">Email</label>
                                <div class="fw-medium small text-break" id="viewPatientEmail">-</div>
                            </div>
                            <div class="col-6">
                                <label class="small text-muted text-uppercase fw-bold">Category</label>
                                <div id="viewPatientStatus">-</div>
                            </div>
                            <div class="col-6">
                                <label class="small text-muted text-uppercase fw-bold">Registered</label>
                                <div class="fw-medium small" id="viewPatientDate">-</div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Visit History -->
                    <div class="col-md-7">
                        <h6 class="fw-bold mb-3">
                            <i class="fas fa-history text-primary me-2"></i>Visit History
                        </h6>
                        <div id="viewPatientHistory">
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-clipboard-list fa-2x mb-2"></i>
                                <p class="mb-0 small">No previous visits</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="createAppointmentForPatient()">
                    <i class="fas fa-plus-circle me-1"></i> Create Appointment
                </button>
            </div>
        </div>
    </div>
</div>


@endsection

@push('scripts')
<script>
    let currentPatientId = null;

    // View Patient Details
    function viewPatient(patient) {
        const modal = new bootstrap.Modal(document.getElementById('viewPatientModal'));
        currentPatientId = patient.id;
        
        document.getElementById('viewPatientInitials').textContent = (patient.first_name[0] + patient.last_name[0]).toUpperCase();
        document.getElementById('viewPatientName').textContent = patient.first_name + ' ' + patient.last_name;
        document.getElementById('viewPatientId').textContent = 'ID: ' + (patient.patient_id || patient.id);
        document.getElementById('viewPatientPhone').textContent = patient.phone || '-';
        document.getElementById('viewPatientEmail').textContent = patient.email || '-';
        document.getElementById('viewPatientDate').textContent = new Date(patient.created_at).toLocaleDateString();
        
        // Category/Status badges
        let statusHtml = '';
        if(patient.is_senior) statusHtml += '<span class="badge bg-secondary me-1">Senior</span>';
        if(patient.is_pwd) statusHtml += '<span class="badge bg-secondary me-1">PWD</span>';
        if(!patient.is_senior && !patient.is_pwd) statusHtml += '<span class="badge bg-light text-dark border">Regular</span>';
        document.getElementById('viewPatientStatus').innerHTML = statusHtml;

        // Visit count badge
        const visitCount = patient.queues_count || 0;
        let visitBadgeHtml = '';
        if (visitCount > 0) {
            visitBadgeHtml = `
                <span class="badge bg-success bg-opacity-10 text-success px-3 py-2">
                    <i class="fas fa-check-circle me-1"></i> Returning Patient (${visitCount} visit${visitCount > 1 ? 's' : ''})
                </span>
            `;
        } else {
            visitBadgeHtml = '<span class="badge bg-info bg-opacity-10 text-info px-3 py-2"><i class="fas fa-user-plus me-1"></i> New Patient</span>';
        }
        document.getElementById('viewPatientVisitBadge').innerHTML = visitBadgeHtml;

        // Visit history
        const historyContainer = document.getElementById('viewPatientHistory');
        if (patient.queues && patient.queues.length > 0) {
            let historyHtml = '<div class="list-group list-group-flush">';
            patient.queues.forEach((queue, index) => {
                const date = new Date(queue.created_at);
                const statusClass = queue.status === 'completed' ? 'success' : 
                                   queue.status === 'cancelled' ? 'danger' : 
                                   queue.status === 'skipped' ? 'warning' : 'primary';
                historyHtml += `
                    <div class="list-group-item px-0 py-2 border-0 ${index > 0 ? 'border-top' : ''}">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-bold text-primary">${queue.queue_number}</span>
                                <span class="text-muted small ms-2">${date.toLocaleDateString()}</span>
                            </div>
                            <span class="badge bg-${statusClass} bg-opacity-10 text-${statusClass}">${queue.status}</span>
                        </div>
                    </div>
                `;
            });
            historyHtml += '</div>';
            historyContainer.innerHTML = historyHtml;
        } else {
            historyContainer.innerHTML = `
                <div class="text-center text-muted py-4">
                    <i class="fas fa-clipboard-list fa-2x mb-2"></i>
                    <p class="mb-0 small">No previous visits</p>
                </div>
            `;
        }

        modal.show();
    }

    // Create appointment for current patient
    function createAppointmentForPatient() {
        if (!currentPatientId) {
            Swal.fire('Error', 'No patient selected.', 'error');
            return;
        }
        
        // Close view modal and open add modal with patient pre-filled
        const viewModal = bootstrap.Modal.getInstance(document.getElementById('viewPatientModal'));
        viewModal.hide();
        
        // Redirect to add patient page with existing patient ID
        Swal.fire({
            title: 'Create New Appointment',
            text: 'This will create a new queue entry for this returning patient.',
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Create Appointment',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#0d6efd'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `{{ route('staff.patients.add') }}?patient_id=${currentPatientId}`;
            }
        });
    }

    // Handle Add Patient Form Submission
    function submitAddPatient() {
        console.log('Manual submission triggered');
        
        const form = document.getElementById('addPatientForm');
        // Find button properly - it's the one with onclick 'submitAddPatient()'
        const btn = document.querySelector('button[onclick="submitAddPatient()"]');
        
        if (!form || !btn) {
            console.error('Form or button not found');
            return;
        }

        // Basic Client-side Validation
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        
        const originalBtnText = btn.innerHTML;
        const alertContainer = document.getElementById('addPatientError');
        
        // Reset State
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        alertContainer.classList.add('d-none');
        alertContainer.textContent = '';
        
        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json().then(data => ({ status: response.status, body: data })))
        .then(({ status, body }) => {
            if (status >= 200 && status < 300 && body.success) {
                // Success
                form.reset();
                
                // Hide Add Modal
                const modalEl = document.getElementById('addPatientModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                modal.hide();
                
                // Show SweetAlert Success with Print Option
                const queueNumber = body.data.queue_number;
                const patientName = body.data.patient_name;
                const ticketUrl = `/queue/${queueNumber}/status`;
                
                Swal.fire({
                    title: 'Registration Successful!',
                    html: `
                        <p class="text-muted small mb-4">Patient has been added to the queue.</p>
                        <div class="bg-light p-3 rounded mb-4 text-center">
                            <div class="small text-uppercase text-muted fw-bold">Queue Number</div>
                            <h1 class="fw-bold display-4 mb-0 text-primary">${queueNumber}</h1>
                            <div class="fw-medium text-dark mt-1">${patientName}</div>
                        </div>
                    `,
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonText: 'Print Ticket',
                    cancelButtonText: 'Done',
                    confirmButtonColor: '#0d6efd',
                    cancelButtonColor: '#6c757d',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.open(ticketUrl, '_blank', 'width=450,height=600');
                        setTimeout(() => window.location.reload(), 500);
                    } else {
                        window.location.reload();
                    }
                });

            } else {
                // Error
                throw new Error(body.message || 'Something went wrong. Please try again.');
            }
        })
        .catch(error => {
            console.error('Submission error:', error);
            alertContainer.textContent = error.message;
            alertContainer.classList.remove('d-none');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalBtnText;
        });
    }
</script>
@endpush
