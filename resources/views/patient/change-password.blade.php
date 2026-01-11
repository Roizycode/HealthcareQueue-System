@extends('layouts.patient')

@section('title', 'Change Password - Smart Healthcare')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <!-- Page Header -->
            <div class="mb-4">
                <h4 class="fw-bold text-dark mb-1">
                    <i class="fas fa-lock text-success me-2"></i>Change Password
                </h4>
                <p class="text-muted small mb-0">Update your password anytime without contacting staff</p>
            </div>
            
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    @foreach($errors->all() as $error)
                        <i class="fas fa-exclamation-circle me-2"></i>{{ $error }}<br>
                    @endforeach
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('patient.change-password.submit') }}">
                        @csrf
                        
                        <!-- Current Password -->
                        <div class="mb-4">
                            <label for="current_password" class="form-label fw-semibold">
                                <i class="fas fa-key text-muted me-1"></i>Current Password
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control form-control-lg" 
                                       id="current_password" name="current_password" 
                                       placeholder="Enter your current password" required
                                       style="border-radius: 12px 0 0 12px; border: 2px solid #e9ecef;">
                                <button type="button" class="btn btn-outline-secondary" 
                                        onclick="togglePassword('current_password')"
                                        style="border-radius: 0 12px 12px 0; border: 2px solid #e9ecef; border-left: 0;">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- New Password -->
                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold">
                                <i class="fas fa-lock text-muted me-1"></i>New Password
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control form-control-lg" 
                                       id="password" name="password" 
                                       placeholder="Enter new password" required
                                       style="border-radius: 12px 0 0 12px; border: 2px solid #e9ecef;">
                                <button type="button" class="btn btn-outline-secondary" 
                                        onclick="togglePassword('password')"
                                        style="border-radius: 0 12px 12px 0; border: 2px solid #e9ecef; border-left: 0;">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Password must be at least 8 characters long
                            </div>
                        </div>
                        
                        <!-- Confirm New Password -->
                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label fw-semibold">
                                <i class="fas fa-check-double text-muted me-1"></i>Confirm New Password
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control form-control-lg" 
                                       id="password_confirmation" name="password_confirmation" 
                                       placeholder="Confirm new password" required
                                       style="border-radius: 12px 0 0 12px; border: 2px solid #e9ecef;">
                                <button type="button" class="btn btn-outline-secondary" 
                                        onclick="togglePassword('password_confirmation')"
                                        style="border-radius: 0 12px 12px 0; border: 2px solid #e9ecef; border-left: 0;">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg" style="border-radius: 12px;">
                                <i class="fas fa-save me-2"></i>Update Password
                            </button>
                            <a href="{{ route('patient.profile') }}" class="btn btn-outline-secondary" style="border-radius: 12px;">
                                Back to Profile
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Security Tips -->
            <div class="card border-0 shadow-sm mt-4" style="border-radius: 16px; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-success mb-3">
                        <i class="fas fa-shield-alt me-2"></i>Password Security Tips
                    </h6>
                    <ul class="list-unstyled mb-0 small text-muted">
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Use a combination of letters, numbers, and symbols
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Make your password at least 8 characters long
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Don't use personal information like birthdays
                        </li>
                        <li>
                            <i class="fas fa-check text-success me-2"></i>
                            Never share your password with anyone
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function togglePassword(id) {
        const input = document.getElementById(id);
        const button = input.nextElementSibling;
        const icon = button.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
@endpush
