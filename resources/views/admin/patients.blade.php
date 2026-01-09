@extends('layouts.admin')

@section('title', 'Manage Patients - HealthQueue')

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Patients</h4>
        <p class="text-muted small mb-0">View and manage registered patients</p>
    </div>
    <a href="{{ route('admin.patients.add') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i> Add Patient
    </a>
</div>

<!-- Search -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form action="{{ route('admin.patients') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-8">
                <div class="position-relative">
                    <i class="fas fa-search position-absolute text-muted" style="top: 50%; left: 12px; transform: translateY(-50%);"></i>
                    <input type="text" name="search" class="form-control form-control-sm ps-5" placeholder="Search by name, phone, or email..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="fas fa-search me-1"></i> Search
                </button>
            </div>
        </form>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show border-0 shadow-sm py-2 small" role="alert">
    <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
    <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Patients Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center" style="font-size: 0.8rem;">
                <thead class="bg-light">
                    <tr>
                        <th class="border-bottom-0">Patient</th>
                        <th class="border-bottom-0">Contact</th>
                        <th class="border-bottom-0">Category</th>
                        <th class="border-bottom-0">Last Visit</th>
                        <th class="border-bottom-0">Visits</th>
                        <th class="border-bottom-0">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($patients as $patient)
                        <tr>
                            <td>
                                <div class="d-inline-flex align-items-center">
                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                        {{ substr($patient->first_name, 0, 1) }}{{ substr($patient->last_name, 0, 1) }}
                                    </div>
                                    <div class="text-start">
                                        <span class="fw-bold">{{ $patient->full_name }}</span>
                                        <small class="text-muted d-block">#{{ $patient->id }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <small class="d-block">{{ $patient->phone }}</small>
                                @if($patient->email)
                                    <small class="text-muted">{{ $patient->email }}</small>
                                @endif
                            </td>
                            <td>
                                @if($patient->is_senior)
                                    <span class="badge bg-purple text-white" style="background: #9c27b0;">Senior</span>
                                @endif
                                @if($patient->is_pwd)
                                    <span class="badge bg-info text-dark">PWD</span>
                                @endif
                                @if(!$patient->is_senior && !$patient->is_pwd)
                                    <span class="badge bg-light text-dark">Regular</span>
                                @endif
                            </td>
                            <td class="text-muted">
                                @if($patient->queues->last())
                                    {{ $patient->queues->last()->created_at->format('M d, Y') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $patient->queues_count }}</span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary" 
                                            onclick='viewPatient(@json($patient))' title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-warning" 
                                            onclick='editPatient(@json($patient))' title="Edit Patient">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" 
                                            onclick='deletePatient({{ $patient->id }})' title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-users-slash fa-2x mb-2 opacity-25"></i>
                                <p class="mb-0 small">No patients found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($patients->hasPages())
    <div class="card-footer bg-white py-2">
        {{ $patients->links() }}
    </div>
    @endif
</div>
@endsection

@push('modals')
<!-- Edit Patient Modal -->
<div class="modal fade" id="editPatientModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form id="editPatientForm" action="" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold">Edit Patient Details</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                         <div class="col-6">
                            <label class="form-label small text-muted">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" id="edit_first_name" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted">Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" id="edit_last_name" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted">Phone <span class="text-danger">*</span></label>
                            <input type="text" name="phone" id="edit_phone" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted">Email</label>
                            <input type="email" name="email" id="edit_email" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted">Date of Birth</label>
                            <input type="date" name="date_of_birth" id="edit_dob" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted">Gender</label>
                            <select name="gender" id="edit_gender" class="form-select">
                                <option value="">Select</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-muted">Address</label>
                            <textarea name="address" id="edit_address" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endpush

@push('scripts')
<script>
    function viewPatient(patient) {
        Swal.fire({
            title: patient.full_name,
            html: `
                <div class="text-start p-3 bg-light rounded-3 mt-2">
                    <p class="mb-1"><strong>Phone:</strong> ${patient.phone}</p>
                    <p class="mb-1"><strong>Email:</strong> ${patient.email || 'N/A'}</p>
                    <p class="mb-1"><strong>Gender:</strong> ${patient.gender ? patient.gender.charAt(0).toUpperCase() + patient.gender.slice(1) : 'N/A'}</p>
                    <p class="mb-1"><strong>Birth Date:</strong> ${patient.date_of_birth || 'N/A'}</p>
                     <p class="mb-0"><strong>Address:</strong> ${patient.address || 'N/A'}</p>
                </div>
            `,
            confirmButtonText: 'Close'
        });
    }

    function editPatient(patient) {
        // Populate form fields
        document.getElementById('edit_first_name').value = patient.first_name;
        document.getElementById('edit_last_name').value = patient.last_name;
        document.getElementById('edit_phone').value = patient.phone;
        document.getElementById('edit_email').value = patient.email || '';
        if(patient.date_of_birth) {
            // Ensure format YYYY-MM-DD
            const d = new Date(patient.date_of_birth);
            const dateString = d.toISOString().split('T')[0];
            document.getElementById('edit_dob').value = dateString;
        } else {
             document.getElementById('edit_dob').value = '';
        }

        document.getElementById('edit_gender').value = patient.gender || '';
        document.getElementById('edit_address').value = patient.address || '';

        // Set form action
        const form = document.getElementById('editPatientForm');
        form.action = `/admin/patients/${patient.id}`;

        // Show modal
        new bootstrap.Modal(document.getElementById('editPatientModal')).show();
    }

    function deletePatient(id) {
        Swal.fire({
            title: 'Delete Patient?',
            text: "You won't be able to revert this! History associated with this patient will also be deleted.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/admin/patients/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': window.csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire(
                            'Deleted!',
                            'Patient has been deleted.',
                            'success'
                        ).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire(
                            'Error!',
                            data.message || 'Something went wrong.',
                            'error'
                        );
                    }
                })
                .catch(error => {
                    Swal.fire(
                        'Error!',
                        'Server Error',
                        'error'
                    );
                });
            }
        })
    }
</script>
@endpush
