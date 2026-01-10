@extends('layouts.patient')

@section('title', 'My Profile - Smart Healthcare')

@section('content')
<!-- Page Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">My Profile</h4>
        <p class="text-muted mb-0">Manage your account information</p>
    </div>
</div>

<div class="row">
    <!-- Profile Card -->
    <div class="col-lg-4 mb-4">
        <div class="patient-card p-4 text-center">
            <div class="rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" 
                 style="width: 90px; height: 90px; background: linear-gradient(135deg, #20C997 0%, #0dcaf0 100%);">
                <span class="text-white fw-bold fs-2">{{ $user->initials }}</span>
            </div>
            <h5 class="fw-bold mb-1">{{ $user->name }}</h5>
            <p class="text-muted mb-3">{{ $user->email }}</p>
            
            @if($patient)
                <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 mb-3">
                    <i class="fas fa-id-card me-1"></i> {{ $patient->patient_id ?? 'P-' . $patient->id }}
                </span>
                
                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    @if($patient->is_senior)
                        <span class="badge bg-secondary">Senior Citizen</span>
                    @endif
                    @if($patient->is_pwd)
                        <span class="badge bg-info">PWD</span>
                    @endif
                </div>
            @endif
            
            <hr class="my-4">
            
            <div class="text-start">
                <div class="mb-3">
                    <label class="small text-muted text-uppercase fw-bold">Member Since</label>
                    <div class="fw-medium">{{ $user->created_at->format('F d, Y') }}</div>
                </div>
                <div class="mb-3">
                    <label class="small text-muted text-uppercase fw-bold">Last Login</label>
                    <div class="fw-medium">{{ $user->last_login_at?->format('M d, Y g:i A') ?? 'N/A' }}</div>
                </div>
                @if($patient)
                <div>
                    <label class="small text-muted text-uppercase fw-bold">Total Visits</label>
                    <div class="fw-medium">{{ $patient->queues()->count() }} appointments</div>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Edit Profile Form -->
    <div class="col-lg-8">
        <div class="patient-card p-4">
            <h6 class="fw-bold mb-4">
                <i class="fas fa-user-edit text-success me-2"></i>Edit Profile
            </h6>
            
            <form action="{{ route('patient.profile.update') }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label small fw-medium">Full Name</label>
                        <input type="text" 
                               name="name" 
                               class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $user->name) }}"
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label small fw-medium">Email Address</label>
                        <input type="email" 
                               class="form-control bg-light" 
                               value="{{ $user->email }}"
                               disabled>
                        <small class="text-muted">Email cannot be changed</small>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label small fw-medium">Phone Number</label>
                        <input type="tel" 
                               name="phone" 
                               class="form-control @error('phone') is-invalid @enderror" 
                               value="{{ old('phone', $user->phone ?? $patient?->phone) }}"
                               placeholder="+639xxxxxxxxx">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <hr class="my-4">
                
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('patient.dashboard') }}" class="btn btn-light">Cancel</a>
                    <button type="submit" class="btn btn-success px-4">
                        <i class="fas fa-save me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Account Security -->
        <div class="patient-card p-4 mt-4">
            <h6 class="fw-bold mb-4">
                <i class="fas fa-shield-alt text-warning me-2"></i>Account Security
            </h6>
            
            <div class="alert alert-light border mb-0">
                <div class="d-flex align-items-center">
                    <i class="fas fa-lock text-muted me-3 fa-lg"></i>
                    <div>
                        <div class="fw-medium">Password</div>
                        <small class="text-muted">To change your password, please contact our reception desk.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
