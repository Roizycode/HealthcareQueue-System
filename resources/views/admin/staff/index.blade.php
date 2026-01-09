@extends('layouts.admin')

@section('title', 'Staff Management - HealthQueue')

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Staff Accounts</h4>
        <p class="text-muted small mb-0">Manage system users and roles</p>
    </div>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addStaffModal">
        <i class="fas fa-plus me-1"></i> Add Staff
    </button>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show border-0 shadow-sm py-2 small" role="alert">
    <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
    <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm py-2 small" role="alert">
    @foreach($errors->all() as $error)
        <div>{{ $error }}</div>
    @endforeach
    <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Staff Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center" style="font-size: 0.8rem;">
                <thead class="bg-light">
                    <tr>
                        <th class="border-bottom-0">Staff ID</th>
                        <th class="border-bottom-0">Name</th>
                        <th class="border-bottom-0">Role</th>
                        <th class="border-bottom-0">Service</th>
                        <th class="border-bottom-0">Status</th>
                        <th class="border-bottom-0">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($staff as $user)
                    <tr>
                        <td class="text-muted">{{ $user->employee_id ?? '#' . $user->id }}</td>
                        <td>
                            <div class="d-inline-flex align-items-center">
                                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-2" style="width: 28px; height: 28px; font-size: 0.7rem;">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                                <div class="text-start">
                                    <span class="fw-bold">{{ $user->name }}</span>
                                    <small class="text-muted d-block">{{ $user->email }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($user->role === 'admin')
                                <span class="badge bg-dark">Admin</span>
                            @else
                                <span class="badge bg-info">Staff</span>
                            @endif
                        </td>
                        <td>
                            @if($user->assignedService)
                                <span class="badge bg-light text-dark border">{{ $user->assignedService->name }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($user->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary" 
                                        onclick='viewStaff(@json($user))' title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button" class="btn btn-outline-warning" 
                                        onclick='editStaff(@json($user))' title="Edit Staff">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('modals')
<!-- Add Staff Modal -->
<div class="modal fade" id="addStaffModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.staff.store') }}" method="POST">
            @csrf
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold">Add New Staff</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small text-muted">Full Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-muted">Email Address</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted">Staff ID</label>
                            <input type="text" name="employee_id" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted">Role</label>
                            <select name="role" class="form-select" required>
                                <option value="staff">Staff</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-muted">Password</label>
                            <input type="password" name="password" class="form-control" required minlength="8">
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-muted">Assign Service</label>
                            <select name="assigned_service_id" class="form-select">
                                <option value="">None / General</option>
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}">{{ $service->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Create Staff</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Edit Staff Modal -->
<div class="modal fade" id="editStaffModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form id="editStaffForm" action="" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold">Edit Staff Member</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small text-muted">Full Name</label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-muted">Email Address</label>
                            <input type="email" name="email" id="edit_email" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted">Staff ID</label>
                            <input type="text" name="employee_id" id="edit_employee_id" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted">Role</label>
                            <select name="role" id="edit_role" class="form-select" required>
                                <option value="staff">Staff</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-muted">Password (Leave blank to keep current)</label>
                            <input type="password" name="password" class="form-control" minlength="8">
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-muted">Assign Service</label>
                            <select name="assigned_service_id" id="edit_assigned_service_id" class="form-select">
                                <option value="">None / General</option>
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}">{{ $service->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="edit_is_active" value="1">
                                <label class="form-check-label" for="edit_is_active">Active Account</label>
                            </div>
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
function viewStaff(user) {
    Swal.fire({
        title: user.name,
        html: `
            <div class="text-start p-3 bg-light rounded-3 mt-2">
                <p class="mb-1"><strong>Role:</strong> ${user.role.toUpperCase()}</p>
                <p class="mb-1"><strong>Email:</strong> ${user.email}</p>
                <p class="mb-1"><strong>Employee ID:</strong> ${user.employee_id || 'N/A'}</p>
                <p class="mb-1"><strong>Assigned Service:</strong> ${user.assigned_service ? user.assigned_service.name : 'None / General'}</p>
                <p class="mb-0"><strong>Status:</strong> ${user.is_active ? '<span class="text-success">Active</span>' : '<span class="text-danger">Inactive</span>'}</p>
            </div>
        `,
        imageUrl: `https://ui-avatars.com/api/?name=${user.name}&background=0D6EFD&color=fff`,
        imageWidth: 80,
        imageHeight: 80,
        imageAlt: 'User Avatar',
        confirmButtonText: 'Close'
    });
}

function editStaff(user) {
    // Populate form fields
    document.getElementById('edit_name').value = user.name;
    document.getElementById('edit_email').value = user.email;
    document.getElementById('edit_employee_id').value = user.employee_id || '';
    document.getElementById('edit_role').value = user.role;
    document.getElementById('edit_assigned_service_id').value = user.assigned_service_id || '';
    document.getElementById('edit_is_active').checked = user.is_active;

    // Set form action
    const form = document.getElementById('editStaffForm');
    form.action = `/admin/staff/${user.id}`; // Ensure route matches resource route

    // Show modal
    new bootstrap.Modal(document.getElementById('editStaffModal')).show();
}
</script>
@endpush
