@extends('layouts.app')

@section('title', 'Join Queue - Smart Healthcare')
@section('description', 'Register through our reception desk to join the healthcare queue.')
@section('hideFooter', true)

@section('content')
<section class="py-5" style="min-height: 85vh; background: var(--hc-bg);">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-xl-6">
                <!-- Header -->
                <div class="text-center mb-4 animate-fadeInUp">
                    <div class="mb-3">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10" style="width: 80px; height: 80px;">
                            <i class="fas fa-hospital-user text-primary fa-2x"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold mb-2" style="font-family: 'Poppins', sans-serif;">Smart Healthcare</h2>
                    <h4 class="text-muted fw-normal mb-3">Queue Management System</h4>
                    <p class="text-muted mb-0">Welcome to our modern virtual queue system. Please register through our reception desk to get started.</p>
                </div>

                <!-- Main Card -->
                <div class="card card-healthcare shadow-lg border-0 animate-fadeInUp" style="animation-delay: 0.2s;">
                    <div class="card-body p-4 p-md-5">
                        <!-- Reception Desk Registration -->
                        <div class="text-center mb-4">
                            <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                <i class="fas fa-user-nurse text-success fa-xl"></i>
                            </div>
                            <h5 class="fw-bold mb-2">Register at Reception Desk</h5>
                            <p class="text-muted small mb-4">Our staff will assist you with registration and queue placement. This ensures accurate information and proper scheduling.</p>
                        </div>

                        <!-- Process Steps -->
                        <div class="mb-4">
                            <h6 class="fw-bold text-uppercase text-muted small mb-3 text-center">
                                <i class="fas fa-list-ol me-1"></i> How It Works
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="text-center p-3 rounded-3 bg-light h-100">
                                        <div class="badge bg-primary rounded-circle mb-2" style="width: 28px; height: 28px; line-height: 20px;">1</div>
                                        <h6 class="fw-bold small mb-1">Visit Reception</h6>
                                        <p class="text-muted small mb-0">Approach the reception desk</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center p-3 rounded-3 bg-light h-100">
                                        <div class="badge bg-primary rounded-circle mb-2" style="width: 28px; height: 28px; line-height: 20px;">2</div>
                                        <h6 class="fw-bold small mb-1">Register</h6>
                                        <p class="text-muted small mb-0">Staff registers your details</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center p-3 rounded-3 bg-light h-100">
                                        <div class="badge bg-primary rounded-circle mb-2" style="width: 28px; height: 28px; line-height: 20px;">3</div>
                                        <h6 class="fw-bold small mb-1">Get Notified</h6>
                                        <p class="text-muted small mb-0">Receive updates via email</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Existing Patient Check -->
                        <div class="text-center">
                            <h6 class="fw-bold mb-3">Already Registered?</h6>
                            <p class="text-muted small mb-3">If you've been here before, check your queue status or view your appointment history.</p>
                            <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
                                <a href="{{ route('queue.check') }}" class="btn btn-primary px-4 py-2 rounded-pill">
                                    <i class="fas fa-search me-2"></i>Check Queue Status
                                </a>
                                <a href="{{ route('patient.login') }}" class="btn btn-outline-secondary px-4 py-2 rounded-pill">
                                    <i class="fas fa-sign-in-alt me-2"></i>Patient Login
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Card -->
                <div class="card card-healthcare mt-4 hover-scale animate-fadeInUp" style="animation-delay: 0.4s;">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="rounded-3 p-3" style="background: var(--hc-primary-bg);">
                                    <i class="fas fa-headset text-primary fa-lg"></i>
                                </div>
                            </div>
                            <div class="col">
                                <h6 class="fw-bold mb-1">Need Assistance?</h6>
                                <p class="text-muted mb-0 small">
                                    Visit our reception desk or call 
                                    <a href="tel:+1234567890" class="text-primary fw-bold text-decoration-none">+1 (234) 567-8900</a>
                                </p>
                            </div>
                            <div class="col-auto d-none d-md-block">
                                <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-sm rounded-pill">
                                    <i class="fas fa-home me-1"></i> Back to Home
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mobile Back Button -->
                <div class="d-md-none mt-3 text-center">
                    <a href="{{ route('home') }}" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="fas fa-home me-1"></i> Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    .animate-fadeInUp {
        animation: fadeInUp 0.6s ease-out forwards;
        opacity: 0;
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
@endpush
