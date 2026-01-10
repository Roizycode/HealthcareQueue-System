@extends('layouts.app')

@section('title', 'Smart Healthcare - Smart Healthcare Queue Management')
@section('description', 'Reduce waiting time. Improve patient experience. Join queues online and get SMS notifications.')

@section('content')
<!-- Hero Section -->
<section class="position-relative py-5 overflow-hidden" style="background: var(--hc-gradient-primary);">
    <!-- Live Background Overlay -->
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: rgba(0,0,0,0.1);"></div>
    
    <div class="container position-relative z-1">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <div class="d-inline-flex align-items-center bg-white bg-opacity-25 rounded-pill px-3 py-1 mb-3">
                    <span class="text-white small fw-bold">Live Queue System</span>
                </div>
                
                <!-- Heading -->
                <h1 class="display-4 fw-bold mb-3 text-white lh-sm" style="font-family: 'Poppins', sans-serif;">
                    Smart Healthcare<br>
                    <span class="text-warning">Queue Management System</span>
                </h1>
                
                <!-- Subtitle -->
                <p class="lead mb-4 text-white opacity-90 mx-auto" style="font-size: 1.1rem; max-width: 700px;">
                    Smart Healthcare is a modern virtual queue system designed for hospitals, clinics, and healthcare centers. 
                    Patients can join queues remotely, monitor their status, and arrive exactly when they are needed.
                </p>
                
                <!-- CTA Buttons -->
                <div class="d-flex flex-wrap justify-content-center gap-3 mb-4">
                    <a href="{{ route('queue.join') }}" class="btn btn-light btn-lg px-4 py-2 rounded-pill fw-bold shadow-lg hover-lift">
                        <i class="fas fa-plus-circle me-2"></i> Join Queue
                    </a>
                    
                    <a href="{{ route('queue.check') }}" class="btn btn-outline-light btn-lg px-4 py-2 rounded-pill fw-bold">
                        <i class="fas fa-search me-2"></i> Check Status
                    </a>
                </div>

                <!-- Stats Row -->
                <div class="row g-3 mt-1 justify-content-center">
                    <div class="col-md-3 col-6">
                        <div class="text-center p-3 rounded-3" style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px);">
                            <h4 class="fw-bold mb-0 text-white" style="font-family: 'Poppins', sans-serif;">{{ $todayServed }}+</h4>
                            <small class="text-white-50" style="font-size: 0.75rem;">Patients Today</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="text-center p-3 rounded-3" style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px);">
                            <h4 class="fw-bold mb-0 text-white" style="font-family: 'Poppins', sans-serif;">{{ $avgWaitTime }}</h4>
                            <small class="text-white-50" style="font-size: 0.75rem;">Avg Wait Time</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="text-center p-3 rounded-3" style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px);">
                            <h4 class="fw-bold mb-0 text-white" style="font-family: 'Poppins', sans-serif;">{{ $rating }}</h4>
                            <small class="text-white-50" style="font-size: 0.75rem;">Patient Rating</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works Section - Compact -->
<section class="py-4 bg-white">
    <div class="container py-4">
        <div class="text-center mb-4">
            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1 rounded-pill mb-2 fw-semibold small">
                <i class="fas fa-magic me-1"></i> Simple Process
            </span>
            <h2 class="fs-2 fw-bold mb-2" style="font-family: 'Poppins', sans-serif;">How It Works</h2>
            <p class="text-muted small mx-auto" style="max-width: 600px;">
                Four simple steps to skip the wait and get served faster
            </p>
        </div>

        <div class="row g-3">
            @foreach([
                ['step' => '1', 'icon' => 'fa-user-nurse', 'title' => 'Visit Reception', 'desc' => 'Approach the reception desk for registration.', 'color' => '#0D6EFD'],
                ['step' => '2', 'icon' => 'fa-clipboard-check', 'title' => 'Get Registered', 'desc' => 'Staff registers your details and assigns queue number.', 'color' => '#20C997'],
                ['step' => '3', 'icon' => 'fa-bell', 'title' => 'Get Notified', 'desc' => 'Receive queue updates and notification when approved.', 'color' => '#FFC107'],
                ['step' => '4', 'icon' => 'fa-check-circle', 'title' => 'Get Served', 'desc' => 'Proceed to counter when called for service.', 'color' => '#198754'],
            ] as $index => $item)
                <div class="col-lg-3 col-md-6">
                    <div class="card card-healthcare h-100 text-center p-3 position-relative hover-scale">
                        <!-- Step Number -->
                        <div class="position-absolute" style="top: 0.5rem; left: 0.5rem;">
                            <span class="badge rounded-circle small" style="width: 24px; height: 24px; line-height: 18px; background: {{ $item['color'] }}; font-size: 0.75rem;">
                                {{ $item['step'] }}
                            </span>
                        </div>
                        <div class="card-body pt-3">
                            <div class="icon-wrap-sm mx-auto mb-3" style="background: {{ $item['color'] }}20; color: {{ $item['color'] }};">
                                <i class="fas {{ $item['icon'] }}"></i>
                            </div>
                            <h6 class="fw-bold mb-2">{{ $item['title'] }}</h6>
                            <p class="text-muted mb-0 small" style="font-size: 0.8rem;">{{ $item['desc'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Services Section - Compact -->
<section class="py-4" style="background: var(--hc-bg);">
    <div class="container py-4">
        <div class="text-center mb-4">
            <span class="badge bg-success bg-opacity-10 text-success px-3 py-1 rounded-pill mb-2 fw-semibold small">
                <i class="fas fa-hospital-alt me-1"></i> Our Services
            </span>
            <h2 class="fs-2 fw-bold mb-2" style="font-family: 'Poppins', sans-serif;">Healthcare Services</h2>
        </div>

        <div class="row g-3">
            @foreach([
                ['icon' => 'fa-stethoscope', 'name' => 'Consultation', 'desc' => 'General medical consultation', 'color' => '#0D6EFD', 'waiting' => rand(5, 15)],
                ['icon' => 'fa-flask', 'name' => 'Laboratory', 'desc' => 'Comprehensive lab tests', 'color' => '#20C997', 'waiting' => rand(3, 10)],
                ['icon' => 'fa-pills', 'name' => 'Pharmacy', 'desc' => 'Quick prescription fulfillment', 'color' => '#9c27b0', 'waiting' => rand(2, 8)],
                ['icon' => 'fa-x-ray', 'name' => 'Radiology', 'desc' => 'Imaging services', 'color' => '#fd7e14', 'waiting' => rand(1, 5)],
            ] as $svc)
                <div class="col-lg-3 col-md-6">
                    <div class="card card-healthcare service-card h-100 hover-scale" data-color="{{ $svc['color'] }}">
                        <div class="card-body p-3 text-center">
                            <div class="icon-wrap-sm" style="background: {{ $svc['color'] }}15; color: {{ $svc['color'] }};">
                                <i class="fas {{ $svc['icon'] }}"></i>
                            </div>
                            <h6 class="fw-bold mb-1">{{ $svc['name'] }}</h6>
                            <p class="text-muted small mb-2" style="font-size: 0.8rem;">{{ $svc['desc'] }}</p>
                            <div class="d-flex justify-content-center gap-2">
                                <span class="badge bg-light text-dark rounded-pill px-2 py-1 small">
                                    <i class="fas fa-users me-1" style="color: {{ $svc['color'] }};"></i>
                                    {{ $svc['waiting'] }} waiting
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="text-center mt-4">
            <a href="{{ route('queue.join') }}" class="btn btn-healthcare btn-primary-hc btn-md rounded-pill px-4">
                <i class="fas fa-plus-circle me-2"></i> Join Queue Now
            </a>
        </div>
    </div>
</section>

<!-- Trust Section / Stats -->
<section class="py-5 bg-white">
    <div class="container py-5">
        <div class="text-center mb-5">
            <span class="badge bg-info bg-opacity-10 text-info px-3 py-2 rounded-pill mb-3 fw-semibold">
                <i class="fas fa-award me-1"></i> Trusted by Healthcare Providers
            </span>
            <h2 class="display-5 fw-bold mb-3" style="font-family: 'Poppins', sans-serif;">Designed for Hospitals & Clinics</h2>
            <p class="text-muted lead mx-auto" style="max-width: 600px;">
                Our system is trusted by healthcare facilities nationwide
            </p>
        </div>

        <div class="row g-4 justify-content-center">
            @foreach([
                ['value' => $totalServed . '+', 'label' => 'Patients Served', 'icon' => 'fa-users', 'color' => '#0D6EFD'],
                ['value' => $avgWaitTime, 'label' => 'Average Wait Time', 'icon' => 'fa-clock', 'color' => '#20C997'],
                ['value' => $uptime, 'label' => 'System Uptime', 'icon' => 'fa-server', 'color' => '#198754'],
                ['value' => $rating, 'label' => 'User Rating', 'icon' => 'fa-star', 'color' => '#FFC107'],
            ] as $stat)
                <div class="col-lg-3 col-md-6">
                    <div class="card card-healthcare stat-card h-100 text-center p-4 hover-scale" style="border-top: 4px solid {{ $stat['color'] }};">
                        <i class="fas {{ $stat['icon'] }} stat-icon" style="color: {{ $stat['color'] }};"></i>
                        <div class="card-body">
                            <h2 class="stat-value mb-2" style="color: {{ $stat['color'] }};">{{ $stat['value'] }}</h2>
                            <p class="stat-label text-muted mb-0">{{ $stat['label'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Features Section - Compact -->
<section class="py-4" style="background: var(--hc-gradient-primary);">
    <div class="container py-4">
        <div class="text-center text-white mb-4">
            <span class="badge bg-white bg-opacity-25 px-3 py-1 rounded-pill mb-2 fw-semibold small">
                <i class="fas fa-star me-1"></i> Key Features
            </span>
            <h2 class="fs-2 fw-bold mb-2" style="font-family: 'Poppins', sans-serif;">Why Choose Smart Healthcare?</h2>
        </div>

        <div class="row g-3">
            @foreach([
                ['icon' => 'fa-user-nurse', 'title' => 'Reception Registration', 'desc' => 'Staff-assisted registration ensures accurate patient information.'],
                ['icon' => 'fa-envelope', 'title' => 'Email Notifications', 'desc' => 'Receive queue updates and approval notifications via email.'],
                ['icon' => 'fa-chart-line', 'title' => 'Live Queue Tracking', 'desc' => 'Monitor queue status and current serving ticket in real time.'],
                ['icon' => 'fa-clock', 'title' => 'Appointment Scheduling', 'desc' => 'Staff schedules your appointment after registration approval.'],
                ['icon' => 'fa-users', 'title' => 'Priority Handling', 'desc' => 'Dedicated lanes for Seniors, PWDs, and Emergency cases.'],
                ['icon' => 'fa-desktop', 'title' => 'Multi-Counter Support', 'desc' => 'Automatically assigns available counters for faster service.'],
            ] as $feature)
                <div class="col-lg-4 col-md-6">
                    <div class="d-flex align-items-center p-3 rounded-4 h-100 hover-lift" style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px);">
                        <div class="rounded-3 p-2 me-3 flex-shrink-0" style="background: rgba(255,255,255,0.2);">
                            <i class="fas {{ $feature['icon'] }} text-white"></i>
                        </div>
                        <div>
                            <h6 class="text-white fw-bold mb-1">{{ $feature['title'] }}</h6>
                            <p class="text-white-50 mb-0 small" style="font-size: 0.8rem;">{{ $feature['desc'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- CTA Section - Compact -->
<section class="py-4 bg-white">
    <div class="container py-4">
        <div class="card card-healthcare border-0 overflow-hidden" style="background: var(--hc-gradient-primary);">
            <!-- Live Background Overlay -->
            <div class="position-absolute top-0 start-0 w-100 h-100" style="background: rgba(0,0,0,0.1);"></div>
            
            <div class="card-body p-4 text-center text-white position-relative z-1">
                <h3 class="fw-bold mb-2" style="font-family: 'Poppins', sans-serif;">
                    Ready to Skip the Queue?
                </h3>
                <p class="mb-3 opacity-90 mx-auto small" style="max-width: 600px;">
                    Join thousands of patients who save time with our smart healthcare queue system
                </p>
                <div class="d-flex flex-wrap justify-content-center gap-2">
                    <a href="{{ route('queue.join') }}" class="btn btn-light btn-md px-4 py-2 rounded-pill fw-bold hover-lift">
                        <i class="fas fa-user-plus me-2"></i> Join Queue Now
                    </a>
                    <a href="{{ route('display') }}" class="btn btn-outline-light btn-md px-4 py-2 rounded-pill fw-bold" target="_blank">
                        <i class="fas fa-tv me-2"></i> View Live Display
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>




@endsection

@push('styles')
<style>
    .icon-wrap-sm {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        margin: 0 auto 1rem auto;
    }
    
    .service-card:hover .icon-wrap-sm {
        transform: scale(1.15);
    }
    
    .min-vh-60 {
        min-height: 60vh;
    }
</style>
@endpush
