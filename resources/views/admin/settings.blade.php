@extends('layouts.admin')

@section('title', 'Settings - HealthQueue')

@section('content')
<!-- Header -->
<div class="mb-4">
    <h4 class="fw-bold text-dark mb-1">Settings</h4>
    <p class="text-muted small mb-0">Manage services, counters, and system configurations</p>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show border-0 shadow-sm py-2 small mb-4" role="alert">
    <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
    <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm py-2 small mb-4" role="alert">
    @foreach($errors->all() as $error)
        <div>{{ $error }}</div>
    @endforeach
    <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row g-4">
    <!-- Services -->
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0"><i class="fas fa-hospital text-primary me-2"></i>Services</h6>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                <i class="fas fa-plus me-1"></i> Add Service
            </button>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.8rem;">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-center border-bottom-0">Name</th>
                                <th class="text-center border-bottom-0">Code</th>
                                <th class="text-center border-bottom-0">Color</th>
                                <th class="text-center border-bottom-0">Avg Time</th>
                                <th class="text-center border-bottom-0">Status</th>
                                <th class="text-center border-bottom-0">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($services as $service)
                            <tr>
                                <td class="text-center fw-bold">{{ $service->name }}</td>
                                <td class="text-center"><span class="badge bg-light text-dark">{{ $service->code }}</span></td>
                                <td class="text-center">
                                    <div class="d-inline-flex align-items-center">
                                        <span class="rounded-circle d-inline-block me-1" style="width: 14px; height: 14px; background: {{ $service->color }};"></span>
                                        <small class="text-muted">{{ $service->color }}</small>
                                    </div>
                                </td>
                                <td class="text-center text-muted">{{ $service->average_service_time }}m</td>
                                <td class="text-center">
                                    @if($service->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick="editService({{ $service->id }}, '{{ $service->name }}', '{{ $service->code }}', '{{ $service->color }}', {{ $service->average_service_time }}, {{ $service->is_active ? 'true' : 'false' }})" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-{{ $service->is_active ? 'warning' : 'success' }}" onclick="toggleService({{ $service->id }}, {{ $service->is_active ? 'false' : 'true' }})" title="{{ $service->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="fas fa-{{ $service->is_active ? 'ban' : 'check' }}"></i>
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
    </div>

    <!-- Counters -->
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0"><i class="fas fa-desktop text-info me-2"></i>Counters</h6>
            <button class="btn btn-info btn-sm text-white" data-bs-toggle="modal" data-bs-target="#addCounterModal">
                <i class="fas fa-plus me-1"></i> Add Counter
            </button>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.8rem;">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-center border-bottom-0">Counter</th>
                                <th class="text-center border-bottom-0">Service</th>
                                <th class="text-center border-bottom-0">Staff</th>
                                <th class="text-center border-bottom-0">Status</th>
                                <th class="text-center border-bottom-0">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($counters as $counter)
                            <tr>
                                <td class="text-center fw-bold">{{ $counter->name }}</td>
                                <td class="text-center">
                                    @if($counter->service)
                                        <span class="badge text-white" style="background: {{ $counter->service->color }};">{{ $counter->service->name }}</span>
                                    @else
                                        <span class="text-muted">All</span>
                                    @endif
                                </td>
                                <td class="text-center text-muted">{{ $counter->assignedStaff->name ?? 'Unassigned' }}</td>
                                <td class="text-center">
                                    @if($counter->status === 'open')
                                        <span class="badge bg-success">Open</span>
                                    @else
                                        <span class="badge bg-secondary">Closed</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick="editCounter({{ $counter->id }}, '{{ $counter->name }}', {{ $counter->service_id ?? 'null' }}, {{ $counter->assigned_staff_id ?? 'null' }})" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-success" onclick="toggleCounter({{ $counter->id }}, 'open')" {{ $counter->status === 'open' ? 'disabled' : '' }} title="Open">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" onclick="toggleCounter({{ $counter->id }}, 'closed')" {{ $counter->status === 'closed' ? 'disabled' : '' }} title="Close">
                                            <i class="fas fa-times"></i>
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
    </div>
</div>
@endsection

@push('modals')
<!-- Add Service Modal -->
<div class="modal fade" id="addServiceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.services.store') }}" method="POST" id="addServiceForm">
            @csrf
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold">Add New Service</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-8">
                            <label class="form-label small text-muted">Service Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g., General Consultation">
                        </div>
                        <div class="col-4">
                            <label class="form-label small text-muted">Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control" required placeholder="e.g., GC" maxlength="5">
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted">Color</label>
                            <input type="color" name="color" class="form-control form-control-color w-100" value="#0d6efd">
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted">Avg Service Time (min)</label>
                            <input type="number" name="average_service_time" class="form-control" value="15" min="1">
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-muted">Description</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Brief description"></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" id="serviceActive" value="1" checked>
                                <label class="form-check-label small" for="serviceActive">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Add Service</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Edit Service Modal -->
<div class="modal fade" id="editServiceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="" method="POST" id="editServiceForm">
            @csrf
            @method('PUT')
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold">Edit Service</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-8">
                            <label class="form-label small text-muted">Service Name</label>
                            <input type="text" name="name" id="editServiceName" class="form-control" required>
                        </div>
                        <div class="col-4">
                            <label class="form-label small text-muted">Code</label>
                            <input type="text" name="code" id="editServiceCode" class="form-control" required maxlength="5">
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted">Color</label>
                            <input type="color" name="color" id="editServiceColor" class="form-control form-control-color w-100">
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted">Avg Time (min)</label>
                            <input type="number" name="average_service_time" id="editServiceTime" class="form-control" min="1">
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" id="editServiceActive" value="1">
                                <label class="form-check-label small" for="editServiceActive">Active</label>
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

<!-- Add Counter Modal -->
<div class="modal fade" id="addCounterModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.counters.store') }}" method="POST" id="addCounterForm">
            @csrf
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold">Add New Counter</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small text-muted">Counter Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g., Counter 1">
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-muted">Assigned Service</label>
                            <select name="service_id" class="form-select">
                                <option value="">All Services</option>
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}">{{ $service->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-muted">Assigned Staff</label>
                            <select name="assigned_staff_id" class="form-select">
                                <option value="">Unassigned</option>
                                @foreach(\App\Models\User::where('role', 'staff')->get() as $staff)
                                    <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-muted">Initial Status</label>
                            <select name="status" class="form-select">
                                <option value="closed">Closed</option>
                                <option value="open">Open</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info btn-sm text-white">Add Counter</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Edit Counter Modal -->
<div class="modal fade" id="editCounterModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="" method="POST" id="editCounterForm">
            @csrf
            @method('PUT')
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold">Edit Counter</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small text-muted">Counter Name</label>
                            <input type="text" name="name" id="editCounterName" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-muted">Assigned Service</label>
                            <select name="service_id" id="editCounterService" class="form-select">
                                <option value="">All Services</option>
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}">{{ $service->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-muted">Assigned Staff</label>
                            <select name="assigned_staff_id" id="editCounterStaff" class="form-select">
                                <option value="">Unassigned</option>
                                @foreach(\App\Models\User::where('role', 'staff')->get() as $staff)
                                    <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                                @endforeach
                            </select>
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
// Edit Service
function editService(id, name, code, color, avgTime, isActive) {
    document.getElementById('editServiceName').value = name;
    document.getElementById('editServiceCode').value = code;
    document.getElementById('editServiceColor').value = color;
    document.getElementById('editServiceTime').value = avgTime;
    document.getElementById('editServiceActive').checked = isActive;
    document.getElementById('editServiceForm').action = `/admin/services/${id}`;
    new bootstrap.Modal(document.getElementById('editServiceModal')).show();
}

// Toggle Service Status
function toggleService(id, activate) {
    Swal.fire({
        title: activate ? 'Activate Service?' : 'Deactivate Service?',
        text: activate ? 'This service will be available for queue.' : 'This service will be hidden from queue.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: activate ? '#198754' : '#ffc107',
        confirmButtonText: activate ? 'Activate' : 'Deactivate'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/admin/services/${id}/toggle`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
                body: JSON.stringify({ is_active: activate })
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Updated!', timer: 1500, showConfirmButton: false }).then(() => location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message });
                }
            });
        }
    });
}

// Edit Counter
function editCounter(id, name, serviceId, staffId) {
    document.getElementById('editCounterName').value = name;
    document.getElementById('editCounterService').value = serviceId || '';
    document.getElementById('editCounterStaff').value = staffId || '';
    document.getElementById('editCounterForm').action = `/admin/counters/${id}`;
    new bootstrap.Modal(document.getElementById('editCounterModal')).show();
}

// Toggle Counter Status
function toggleCounter(id, status) {
    Swal.fire({
        title: status === 'open' ? 'Open Counter?' : 'Close Counter?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: status === 'open' ? '#198754' : '#dc3545',
        confirmButtonText: status === 'open' ? '<i class="fas fa-check me-1"></i> Open' : '<i class="fas fa-times me-1"></i> Close'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/admin/counters/${id}/status/${status}`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Updated!', timer: 1500, showConfirmButton: false }).then(() => location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message });
                }
            });
        }
    });
}
</script>
@endpush
