@extends('layouts.staff')

@section('title', 'Add Patient - HealthQueue Staff')

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Add Patient</h4>
        <p class="text-muted small mb-0">Register a walk-in patient to the queue</p>
    </div>
    <a href="{{ route('staff.patients') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i> Back
    </a>
</div>

<form action="{{ route('staff.patients.register') }}" method="POST" id="addPatientForm">
    @csrf
    <div class="row g-4">
        <!-- Patient Information -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-user text-primary me-2"></i>Patient Info</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label small text-muted">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted">Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted">Phone <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">+63</span>
                                <input type="tel" id="phone_display" class="form-control border-start-0 ps-0" placeholder="9xxxxxxxxx" maxlength="10" minlength="10" pattern="9[0-9]{9}" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="patient@email.com">
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted">Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted">Gender</label>
                            <select name="gender" class="form-select">
                                <option value="">Select</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-muted">Address</label>
                            <textarea name="address" class="form-control" rows="2" placeholder="Complete address"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Queue Details -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-hospital text-primary me-2"></i>Service</h6>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        @foreach($services ?? \App\Models\Service::active()->get() as $service)
                        <div class="col-6">
                            <label class="d-block">
                                <input type="radio" name="service_id" value="{{ $service->id }}" class="d-none" {{ $loop->first ? 'checked' : '' }}>
                                <div class="border rounded p-3 text-center service-option" style="cursor: pointer;">
                                    <span class="d-inline-block rounded-circle mb-2" style="width: 16px; height: 16px; background: {{ $service->color }};"></span>
                                    <div class="fw-bold small">{{ $service->name }}</div>
                                    <small class="text-muted">{{ $service->code }}</small>
                                </div>
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-star text-primary me-2"></i>Priority</h6>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        @foreach($priorities ?? \App\Models\Priority::orderBy('level')->get() as $priority)
                        <div class="col-6">
                            <label class="d-block">
                                <input type="radio" name="priority_id" value="{{ $priority->id }}" class="d-none" {{ $priority->code === 'REG' ? 'checked' : '' }}>
                                <div class="border rounded p-2 text-center priority-option" style="cursor: pointer;">
                                    <span class="fw-bold small">{{ $priority->name }}</span>
                                </div>
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus me-2"></i>Register Patient
                </button>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.service-option').forEach(opt => {
    opt.addEventListener('click', function() {
        document.querySelectorAll('.service-option').forEach(o => o.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10'));
        this.classList.add('border-primary', 'bg-primary', 'bg-opacity-10');
    });
    // Initialize checked state
    if (opt.closest('label').querySelector('input').checked) {
        opt.classList.add('border-primary', 'bg-primary', 'bg-opacity-10');
    }
});

document.querySelectorAll('.priority-option').forEach(opt => {
    opt.addEventListener('click', function() {
        document.querySelectorAll('.priority-option').forEach(o => o.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10'));
        this.classList.add('border-primary', 'bg-primary', 'bg-opacity-10');
    });
    // Initialize checked state
    if (opt.closest('label').querySelector('input').checked) {
        opt.classList.add('border-primary', 'bg-primary', 'bg-opacity-10');
    }
});

document.getElementById('addPatientForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    // Construct FormData with +63
    const formData = new FormData(this);
    const phoneInput = document.getElementById('phone_display').value;
    formData.append('phone', '+63' + phoneInput);

    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Patient Registered!',
                html: `Queue Number: <strong class="text-primary fs-3">${data.data.queue_number}</strong>`,
                showCancelButton: true,
                confirmButtonText: 'Print Ticket',
                cancelButtonText: 'Done'
            }).then(result => {
                if (result.isConfirmed) window.open(`/queue/${data.data.queue_number}/ticket`, '_blank');
                window.location.href = '{{ route("staff.patients") }}';
            });
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: data.message });
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-plus me-2"></i>Register Patient';
        }
    })
    .catch(error => {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Something went wrong.' });
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-plus me-2"></i>Register Patient';
    });
});
</script>
@endpush
