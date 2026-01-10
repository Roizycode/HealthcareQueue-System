@extends('layouts.admin')

@section('title', 'Edit Patient - Smart Healthcare')

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Edit Patient</h4>
        <p class="text-muted small mb-0">Update patient informaton</p>
    </div>
    <a href="{{ route('admin.patients') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i> Back
    </a>
</div>

<form action="{{ route('admin.patients.update', $patient->id) }}" method="POST" id="editPatientForm">
    @csrf
    @method('PUT')
    <div class="row g-4 justify-content-center">
        <!-- Patient Information -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-user-edit text-primary me-2"></i>Patient Details</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label small text-muted">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $patient->first_name) }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted">Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $patient->last_name) }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted">Phone <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $patient->phone) }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $patient->email) }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted">Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', $patient->date_of_birth?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted">Gender</label>
                            <select name="gender" class="form-select">
                                <option value="">Select</option>
                                <option value="male" {{ (old('gender', $patient->gender) == 'male') ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ (old('gender', $patient->gender) == 'female') ? 'selected' : '' }}>Female</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-muted">Address</label>
                            <textarea name="address" class="form-control" rows="2">{{ old('address', $patient->address) }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white py-3 text-end">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-2"></i>Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
