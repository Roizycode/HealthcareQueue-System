@extends('layouts.app')

@section('title', 'Admin Login - HealthQueue')
@section('hideHeader', true)
@section('hideFooter', true)

@section('content')
<div class="login-wrapper">
    <!-- Animated Background Elements (Matches Landing Page) -->
    <div class="position-absolute w-100 h-100" style="overflow: hidden;">
        <div class="position-absolute rounded-circle animate-float" style="width: 400px; height: 400px; background: rgba(255,255,255,0.05); top: -100px; right: -100px;"></div>
        <div class="position-absolute rounded-circle animate-float" style="width: 250px; height: 250px; background: rgba(255,255,255,0.03); bottom: 50px; left: 5%; animation-delay: 1s;"></div>
        <div class="position-absolute rounded-circle" style="width: 80px; height: 80px; background: rgba(255,255,255,0.05); bottom: 30%; left: 20%;"></div>
    </div>

    <div class="container h-100 position-relative z-1 py-5">
        <div class="row h-100 justify-content-center align-items-center">
            <div class="col-md-6 col-lg-5">
                
                <!-- Main Login Card -->
                <div class="card card-healthcare border-0 shadow-lg rounded-4 animate-fadeInUp mt-5">
                    <div class="card-body p-4 p-md-5">
                        
                        <!-- Brand Header -->
                        <div class="text-center mb-4">
                            <h3 class="fw-bold text-dark mb-1" style="font-family: 'Poppins', sans-serif;">
                                <i class="fas fa-user-shield text-primary me-2"></i>Admin Access
                            </h3>
                            <p class="text-muted small">Secure login for administrators</p>
                        </div>

                        <!-- Alerts handled globally -->

                        <form method="POST" action="{{ route('admin.login.submit') }}">
                            @csrf

                            <!-- Email Input -->
                            <div class="form-group mb-3">
                                <label class="form-label text-muted small fw-bold text-uppercase" style="font-size: 0.75rem;">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted px-3 rounded-start-3">
                                        <i class="fas fa-envelope"></i>
                                    </span>
                                    <input type="email" 
                                           name="email" 
                                           class="form-control border-start-0 ps-0 bg-light @error('email') is-invalid @enderror" 
                                           value="{{ old('email') }}"
                                           placeholder="admin@healthqueue.com"
                                           style="height: 48px;"
                                           required 
                                           autofocus>
                                </div>
                                @error('email')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Password Input -->
                            <div class="form-group mb-4">
                                <label class="form-label text-muted small fw-bold text-uppercase" style="font-size: 0.75rem;">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted px-3 rounded-start-3">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" 
                                           name="password" 
                                           class="form-control border-start-0 ps-0 bg-light @error('password') is-invalid @enderror"
                                           placeholder="••••••••"
                                           style="height: 48px;"
                                           required>
                                </div>
                                @error('password')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Keep me signed in -->
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                    <label class="form-check-label small text-muted" for="remember">Keep me signed in</label>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="d-grid">
                                <button type="submit" class="btn btn-dark btn-lg rounded-pill fw-bold shadow-sm hover-lift" 
                                        style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); border: none;">
                                    <i class="fas fa-key me-2"></i>Sign In as Admin
                                </button>
                            </div>

                            <!-- Back Link -->
                            <div class="text-center mt-4">
                                <a href="{{ route('home') }}" class="text-decoration-none small text-white opacity-75 hover-opacity-100">
                                    <i class="fas fa-arrow-left me-1"></i> Return to Homepage
                                </a>
                            </div>
                            
                            <!-- Staff Login Link -->
                            <div class="text-center mt-2">
                                <a href="{{ route('login') }}" class="text-decoration-none small text-white opacity-50 hover-opacity-100">
                                    Not an Admin? Staff Login
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                     <p class="text-white small opacity-50">&copy; {{ date('Y') }} HealthQueue System</p>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
    .login-wrapper {
        min-height: 100vh;
        width: 100%;
        position: relative;
        /* Verified Admin Dark Theme */
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Hide scrollbar for standard browsers */
    body::-webkit-scrollbar { 
        display: none; 
    }
    body { 
        -ms-overflow-style: none; 
        scrollbar-width: none; 
    }
    
    .card-healthcare {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .form-control:focus {
        box-shadow: none;
        background-color: #fff !important;
        border-color: #334155;
    }
    
    .input-group-text {
        border-color: #dee2e6;
    }
    
    .form-control {
        border-color: #dee2e6;
    }
    
    .hover-lift:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0, 0.4) !important;
    }
    
    .hover-opacity-100:hover {
        opacity: 1 !important;
    }
</style>
@endsection
