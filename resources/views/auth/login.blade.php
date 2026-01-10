@extends('layouts.app')

@section('title', 'Login - Smart Healthcare')
@section('hideHeader', true)
@section('hideFooter', true)

@section('content')
<div class="login-wrapper">
    <!-- Animated Background Elements (Matches Landing Page) -->
    <div class="position-absolute w-100 h-100" style="overflow: hidden;">
        <div class="position-absolute rounded-circle animate-float" style="width: 400px; height: 400px; background: rgba(255,255,255,0.1); top: -100px; right: -100px;"></div>
        <div class="position-absolute rounded-circle animate-float" style="width: 250px; height: 250px; background: rgba(255,255,255,0.08); bottom: 50px; left: 5%; animation-delay: 1s;"></div>
        <div class="position-absolute rounded-circle" style="width: 80px; height: 80px; background: rgba(255,255,255,0.1); bottom: 30%; left: 20%;"></div>
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
                                <i class="fas fa-user-nurse text-primary me-2"></i>Staff Portal
                            </h3>
                            <p class="text-muted small">Sign in to manage queues and patients</p>
                        </div>

                        <form method="POST" action="{{ route('login') }}">
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
                                           placeholder="staff@smarthealthcare.com"
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
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-bold shadow-sm hover-lift" 
                                        style="background: linear-gradient(135deg, #0D6EFD 0%, #0dcaf0 100%); border: none;">
                                    Sign In <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>

                            <!-- Back Link -->
                            <div class="text-center mt-4">
                                <a href="{{ route('home') }}" class="text-decoration-none small text-muted">
                                    <i class="fas fa-arrow-left me-1"></i> Return to Homepage
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                     <p class="text-white small opacity-50">&copy; {{ date('Y') }} Smart Healthcare System</p>
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
        /* Matches Landing Page Hero Gradient */
        background: linear-gradient(135deg, #0D6EFD 0%, #0dcaf0 50%, #20C997 100%);
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
        border-color: #0dcaf0;
    }
    
    .input-group-text {
        border-color: #dee2e6;
    }
    
    .form-control {
        border-color: #dee2e6;
    }
    
    .hover-lift:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(13, 202, 240, 0.4) !important;
    }
    
    .hover-opacity-100:hover {
        opacity: 1 !important;
    }
</style>
@endsection
