@extends('layouts.app')

@section('title', 'Register - HealthCare Queue System')

@section('content')
<section class="py-5" style="min-height: 80vh; background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="card card-custom border-0 shadow-lg">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                                 style="width: 80px; height: 80px; background: var(--secondary-gradient);">
                                <i class="fas fa-user-plus fa-2x text-white"></i>
                            </div>
                            <h3 class="fw-bold">Create Account</h3>
                            <p class="text-muted">Join our healthcare queue system</p>
                        </div>

                        <form method="POST" action="{{ route('register') }}">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-user"></i></span>
                                    <input type="text" 
                                           name="name" 
                                           class="form-control form-control-custom @error('name') is-invalid @enderror" 
                                           value="{{ old('name') }}"
                                           placeholder="John Doe"
                                           required>
                                </div>
                                @error('name')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-envelope"></i></span>
                                    <input type="email" 
                                           name="email" 
                                           class="form-control form-control-custom @error('email') is-invalid @enderror" 
                                           value="{{ old('email') }}"
                                           placeholder="your@email.com"
                                           required>
                                </div>
                                @error('email')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Phone Number (Optional)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">+63</span>
                                    <input type="tel" 
                                           id="phone_display"
                                           class="form-control form-control-custom @error('phone') is-invalid @enderror" 
                                           value="{{ old('phone') ? substr(old('phone'), -10) : '' }}"
                                           placeholder="9xxxxxxxxx"
                                           maxlength="10"
                                           minlength="10"
                                           pattern="9[0-9]{9}"
                                           oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10); document.getElementById('phone_hidden').value = this.value ? '+63' + this.value : '';">
                                </div>
                                <input type="hidden" name="phone" id="phone_hidden" value="{{ old('phone') }}">
                                @error('phone')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-lock"></i></span>
                                    <input type="password" 
                                           name="password" 
                                           class="form-control form-control-custom @error('password') is-invalid @enderror"
                                           placeholder="••••••••"
                                           required>
                                </div>
                                @error('password')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Confirm Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-lock"></i></span>
                                    <input type="password" 
                                           name="password_confirmation" 
                                           class="form-control form-control-custom"
                                           placeholder="••••••••"
                                           required>
                                </div>
                            </div>

                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-secondary-gradient btn-lg rounded-pill">
                                    <i class="fas fa-user-plus me-2"></i> Create Account
                                </button>
                            </div>

                            <p class="text-center text-muted mb-0">
                                Already have an account? 
                                <a href="{{ route('login') }}" class="text-success fw-bold text-decoration-none">Sign In</a>
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
