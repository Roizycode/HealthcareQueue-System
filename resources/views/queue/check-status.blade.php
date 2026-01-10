@extends('layouts.app')

@section('title', 'Check Queue Status - Smart Healthcare')

@section('content')
<section class="py-5" style="min-height: 100vh; background: linear-gradient(135deg, #0D6EFD 0%, #0dcaf0 50%, #20C997 100%);">
    <div class="container py-3">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <!-- Header -->
                <div class="text-center mb-4 animate-fadeInUp">
                    <span class="badge bg-white bg-opacity-25 text-white px-3 py-1 rounded-pill mb-2 fw-semibold small">
                        <i class="fas fa-search me-1"></i>Queue Status
                    </span>
                    <h3 class="fw-bold mb-2 text-white" style="font-family: 'Poppins', sans-serif;">Check Your Queue</h3>
                    <p class="text-white-50 small">Enter your queue number or phone number to check your current position</p>
                </div>

                <!-- Search Card -->
                <div class="card card-healthcare shadow animate-fadeInUp" style="animation-delay: 0.2s;">
                    <div class="card-body p-4">
                        <!-- Error alerts handled globally via SweetAlert -->

                        <form action="{{ route('queue.lookup') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label fw-semibold mb-1">Queue Number or Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 px-3">
                                        <i class="fas fa-search text-primary"></i>
                                    </span>
                                    <input type="text" 
                                           name="search" 
                                           class="form-control form-control-healthcare border-start-0 @error('search') is-invalid @enderror" 
                                           value="{{ old('search') }}"
                                           placeholder="e.g., CON-001 or 09123456789"
                                           required
                                           autofocus
                                           style="padding: 0.75rem;">
                                </div>
                                @error('search')
                                    <div class="text-danger small mt-2">{{ $message }}</div>
                                @enderror
                                <small class="text-muted mt-2 d-block" style="font-size: 0.75rem;">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Enter the queue number exactly as shown on your ticket
                                </small>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-healthcare btn-primary-hc py-2 rounded-pill d-flex justify-content-center align-items-center">
                                    <i class="fas fa-search me-2"></i>Check Status
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Help Cards -->
                <div class="row g-3 mt-3">
                    <div class="col-md-6">
                        <div class="card card-healthcare h-100 hover-scale animate-fadeInUp" style="animation-delay: 0.3s;">
                            <div class="card-body p-3 text-center">
                                <div class="rounded-3 d-inline-flex align-items-center justify-content-center mb-2" 
                                     style="width: 40px; height: 40px; background: var(--hc-primary-bg);">
                                    <i class="fas fa-ticket-alt text-primary small"></i>
                                </div>
                                <h6 class="fw-bold mb-1">Lost Your Ticket?</h6>
                                <p class="text-muted small mb-0" style="font-size: 0.8rem;">Enter your phone number to find your queue position</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-healthcare h-100 hover-scale animate-fadeInUp" style="animation-delay: 0.4s;">
                            <div class="card-body p-3 text-center">
                                <div class="rounded-3 d-inline-flex align-items-center justify-content-center mb-2" 
                                     style="width: 40px; height: 40px; background: var(--hc-secondary-bg);">
                                    <i class="fas fa-envelope text-success small"></i>
                                </div>
                                <h6 class="fw-bold mb-1">Email Notifications</h6>
                                <p class="text-muted small mb-0" style="font-size: 0.8rem;">You'll receive email updates about your queue status</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Not Registered Yet -->
                <div class="card card-healthcare mt-3 hover-scale animate-fadeInUp" style="animation-delay: 0.5s; background: linear-gradient(135deg, #0D6EFD 0%, #0dcaf0 100%); border: none;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="rounded-3 p-2 me-3" style="background: rgba(255,255,255,0.2);">
                                <i class="fas fa-user-plus text-white"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-0 text-white">Not in queue yet?</h6>
                                <p class="text-white-50 mb-0 small" style="font-size: 0.8rem;">Join the queue online and skip the wait</p>
                            </div>
                            <a href="{{ route('queue.join') }}" class="btn btn-light btn-sm rounded-pill px-3">
                                <i class="fas fa-plus me-1"></i>Join Now
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Live Display Link -->
                <div class="text-center mt-3 animate-fadeInUp" style="animation-delay: 0.6s;">
                    <a href="{{ route('display') }}" class="text-white text-decoration-none opacity-75" target="_blank">
                        <i class="fas fa-tv me-1"></i>View Live Queue Display
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
