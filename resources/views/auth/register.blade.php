@extends('layouts.app')

@section('title', 'Register - Smart Healthcare')
@section('hideHeader', true)
@section('hideFooter', true)

@section('content')
<div class="register-wrapper">
    <!-- Animated Background Elements -->
    <div class="position-absolute w-100 h-100" style="overflow: hidden;">
        <div class="position-absolute rounded-circle animate-float" style="width: 400px; height: 400px; background: rgba(255,255,255,0.1); top: -100px; right: -100px;"></div>
        <div class="position-absolute rounded-circle animate-float" style="width: 250px; height: 250px; background: rgba(255,255,255,0.08); bottom: 50px; left: 5%; animation-delay: 1s;"></div>
        <div class="position-absolute rounded-circle" style="width: 80px; height: 80px; background: rgba(255,255,255,0.1); bottom: 30%; left: 20%;"></div>
    </div>

    <div class="container h-100 position-relative z-1 py-5">
        <div class="row h-100 justify-content-center align-items-center">
            <div class="col-lg-5 col-md-8">
                
                <!-- Main Registration Card -->
                <div class="card card-healthcare border-0 shadow-lg rounded-4 animate-fadeInUp">
                    <div class="card-body p-4 p-md-5">
                        
                        <!-- Brand Header -->
                        <div class="text-center mb-4">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                                 style="width: 70px; height: 70px; background: linear-gradient(135deg, #20C997 0%, #0dcaf0 100%);">
                                <i class="fas fa-user-plus fa-xl text-white"></i>
                            </div>
                            <h3 class="fw-bold text-dark mb-1" style="font-family: 'Poppins', sans-serif;">
                                Create Account
                            </h3>
                            <p class="text-muted small">Join Smart Healthcare to manage your appointments</p>
                        </div>

                        <form method="POST" action="{{ route('register') }}">
                            @csrf

                            <!-- Full Name -->
                            <div class="form-group mb-3">
                                <label class="form-label text-muted small fw-bold text-uppercase" style="font-size: 0.75rem;">Full Name</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted px-3 rounded-start-3">
                                        <i class="fas fa-user"></i>
                                    </span>
                                    <input type="text" 
                                           name="name" 
                                           class="form-control border-start-0 ps-0 bg-light @error('name') is-invalid @enderror" 
                                           value="{{ old('name') }}"
                                           placeholder="John Doe"
                                           style="height: 48px;"
                                           required>
                                </div>
                                @error('name')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Email -->
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
                                           placeholder="your@email.com"
                                           style="height: 48px;"
                                           required>
                                </div>
                                @error('email')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Phone -->
                            <div class="form-group mb-3">
                                <label class="form-label text-muted small fw-bold text-uppercase" style="font-size: 0.75rem;">Phone Number (Optional)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted px-3 rounded-start-3 fw-medium">+63</span>
                                    <input type="tel" 
                                           id="phone_display"
                                           class="form-control border-start-0 ps-1 bg-light @error('phone') is-invalid @enderror" 
                                           value="{{ old('phone') ? substr(old('phone'), -10) : '' }}"
                                           placeholder="9xxxxxxxxx"
                                           maxlength="10"
                                           minlength="10"
                                           pattern="9[0-9]{9}"
                                           style="height: 48px;"
                                           oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10); document.getElementById('phone_hidden').value = this.value ? '+63' + this.value : '';">
                                </div>
                                <input type="hidden" name="phone" id="phone_hidden" value="{{ old('phone') }}">
                                @error('phone')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Password -->
                            <div class="form-group mb-3">
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

                            <!-- Confirm Password -->
                            <div class="form-group mb-4">
                                <label class="form-label text-muted small fw-bold text-uppercase" style="font-size: 0.75rem;">Confirm Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted px-3 rounded-start-3">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" 
                                           name="password_confirmation" 
                                           class="form-control border-start-0 ps-0 bg-light"
                                           placeholder="••••••••"
                                           style="height: 48px;"
                                           required>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="d-grid">
                                <button type="submit" class="btn btn-lg rounded-pill fw-bold shadow-sm hover-lift" 
                                        style="background: linear-gradient(135deg, #20C997 0%, #0dcaf0 100%); border: none; color: white;">
                                    <i class="fas fa-user-plus me-2"></i> Create Account
                                </button>
                            </div>

                            <!-- Login Link -->
                            <div class="text-center mt-4 pt-3 border-top">
                                <p class="text-muted small mb-2">Already have an account?</p>
                                <a href="{{ route('patient.login') }}" class="btn btn-outline-success rounded-pill px-4">
                                    <i class="fas fa-sign-in-alt me-1"></i> Sign In
                                </a>
                            </div>

                            <!-- Back Link -->
                            <div class="text-center mt-3">
                                <a href="{{ route('home') }}" class="text-decoration-none small text-muted">
                                    Return to Homepage
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
    .register-wrapper {
        min-height: 100vh;
        width: 100%;
        position: relative;
        /* Patient-specific gradient (teal/green theme) - matches patient login */
        background: linear-gradient(135deg, #20C997 0%, #0dcaf0 50%, #0D6EFD 100%);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
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
        border-color: #20C997;
    }
    
    .input-group-text {
        border-color: #dee2e6;
    }
    
    .form-control {
        border-color: #dee2e6;
    }
    
    .hover-lift:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(32, 201, 151, 0.4) !important;
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-20px); }
    }
    
    .animate-float {
        animation: float 6s ease-in-out infinite;
    }
</style>
@endsection
