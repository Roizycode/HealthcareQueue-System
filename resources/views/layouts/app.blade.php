<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'HealthQueue - Smart Healthcare Queue Management')</title>
    <meta name="description" content="@yield('description', 'Modern Healthcare Queue Management System - Join queues online, track your position, and receive notifications')">
    <link rel="icon" type="image/png" href="{{ asset('image/Iconlogo.png') }}">

    <!-- Fonts - Poppins & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Custom Healthcare Design System -->
    <style>
        /*========================================
          COLOR PALETTE - Healthcare System
        ========================================*/
        :root {
            /* Primary Colors */
            --hc-primary: #0D6EFD;
            --hc-primary-dark: #0b5ed7;
            --hc-primary-light: #3d8bfd;
            --hc-primary-bg: #e7f1ff;
            
            /* Secondary - Teal Green */
            --hc-secondary: #20C997;
            --hc-secondary-dark: #1aa179;
            --hc-secondary-bg: #d1f2eb;
            
            /* Accent - Soft Cyan */
            --hc-accent: #E7F5FF;
            --hc-accent-dark: #cce5ff;
            
            /* Status Colors */
            --hc-emergency: #DC3545;
            --hc-warning: #FFC107;
            --hc-success: #198754;
            --hc-info: #0dcaf0;
            
            /* Neutrals */
            --hc-bg: #F8F9FA;
            --hc-bg-dark: #e9ecef;
            --hc-card: #FFFFFF;
            --hc-text: #212529;
            --hc-text-muted: #6c757d;
            --hc-border: #dee2e6;
            
            /* Gradients */
            --hc-gradient-primary: linear-gradient(135deg, #0D6EFD 0%, #0dcaf0 100%);
            --hc-gradient-secondary: linear-gradient(135deg, #20C997 0%, #0D6EFD 100%);
            --hc-gradient-emergency: linear-gradient(135deg, #DC3545 0%, #fd7e14 100%);
            --hc-gradient-dark: linear-gradient(135deg, #212529 0%, #495057 100%);
            
            /* Shadows */
            --hc-shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.08);
            --hc-shadow-md: 0 4px 16px rgba(0, 0, 0, 0.1);
            --hc-shadow-lg: 0 8px 32px rgba(0, 0, 0, 0.12);
            --hc-shadow-hover: 0 12px 40px rgba(13, 110, 253, 0.2);
            
            /* Border Radius */
            --hc-radius-sm: 8px;
            --hc-radius-md: 12px;
            --hc-radius-lg: 16px;
            --hc-radius-xl: 24px;
        }

        /* Hide scrollbar globally */
        ::-webkit-scrollbar { display: none; }
        * { -ms-overflow-style: none; scrollbar-width: none; }

        /*========================================
          TYPOGRAPHY
        ========================================*/
        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        h1, h2, h3, h4, h5, h6, .heading {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            color: var(--hc-text);
        }

        body {
            background: var(--hc-bg);
            color: var(--hc-text);
            font-weight: 400;
            line-height: 1.6;
        }

        /*========================================
          NAVBAR
        ========================================*/
        .navbar-healthcare {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            box-shadow: var(--hc-shadow-sm);
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }

        .navbar-healthcare.navbar-hidden {
            transform: translateY(-100%);
        }

        .navbar-brand {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--hc-primary) !important;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .navbar-brand i {
            font-size: 1.25rem;
        }

        .nav-link {
            font-weight: 500;
            color: var(--hc-text) !important;
            padding: 0.5rem 1rem !important;
            border-radius: var(--hc-radius-sm);
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: var(--hc-primary) !important;
            background: var(--hc-primary-bg);
        }

        .nav-link i {
            margin-right: 0.25rem;
        }

        /*========================================
          BUTTONS
        ========================================*/
        .btn-healthcare {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border-radius: var(--hc-radius-md);
            border: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary-hc {
            background: var(--hc-gradient-primary);
            color: white;
            box-shadow: 0 4px 15px rgba(13, 110, 253, 0.35);
        }

        .btn-primary-hc:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(13, 110, 253, 0.45);
            color: white;
        }

        .btn-secondary-hc {
            background: var(--hc-gradient-secondary);
            color: white;
            box-shadow: 0 4px 15px rgba(32, 201, 151, 0.35);
        }

        .btn-secondary-hc:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(32, 201, 151, 0.45);
            color: white;
        }

        .btn-outline-hc {
            background: transparent;
            border: 2px solid var(--hc-primary);
            color: var(--hc-primary);
            box-shadow: none;
        }

        .btn-outline-hc:hover {
            background: var(--hc-primary);
            color: white;
            transform: translateY(-2px);
        }

        .btn-emergency {
            background: var(--hc-gradient-emergency);
            color: white;
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.35);
        }

        /*========================================
          CARDS - PREMIUM WITH HOVER ZOOM
        ========================================*/
        .card-healthcare {
            background: var(--hc-card);
            border: none;
            border-radius: var(--hc-radius-lg);
            box-shadow: var(--hc-shadow-sm);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        .card-healthcare:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: var(--hc-shadow-hover);
        }

        .card-healthcare .card-body {
            padding: 1.5rem;
        }

        /* Service Cards */
        .service-card {
            text-align: center;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .service-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--hc-gradient-primary);
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .service-card:hover::before {
            transform: scaleX(1);
        }

        .service-card .icon-wrap {
            width: 70px;
            height: 70px;
            border-radius: var(--hc-radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.75rem;
            transition: all 0.4s ease;
        }

        .service-card:hover .icon-wrap {
            transform: scale(1.1);
        }

        /* Stats Cards */
        .stat-card {
            position: relative;
            overflow: hidden;
        }

        .stat-card .stat-icon {
            position: absolute;
            right: -10px;
            bottom: -10px;
            font-size: 5rem;
            opacity: 0.1;
            transition: all 0.4s ease;
        }

        .stat-card:hover .stat-icon {
            opacity: 0.2;
            transform: scale(1.1) rotate(5deg);
        }

        .stat-card .stat-value {
            font-family: 'Poppins', sans-serif;
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .stat-card .stat-label {
            font-size: 0.875rem;
            font-weight: 500;
            opacity: 0.9;
        }

        /*========================================
          QUEUE NUMBER DISPLAY
        ========================================*/
        .queue-number-display {
            font-family: 'Poppins', sans-serif;
            font-size: 4.5rem;
            font-weight: 800;
            background: var(--hc-gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -2px;
        }

        .queue-number-large {
            font-size: 6rem;
            letter-spacing: -3px;
        }

        /*========================================
          STATUS BADGES
        ========================================*/
        .badge-status {
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-waiting {
            background: var(--hc-primary-bg);
            color: var(--hc-primary);
        }

        .badge-called {
            background: linear-gradient(135deg, #FFC107 0%, #fd7e14 100%);
            color: #212529;
        }

        .badge-serving {
            background: var(--hc-gradient-primary);
            color: white;
        }

        .badge-completed {
            background: var(--hc-gradient-secondary);
            color: white;
        }

        .badge-emergency {
            background: var(--hc-gradient-emergency);
            color: white;
            animation: pulse-emergency 2s infinite;
        }

        @keyframes pulse-emergency {
            0%, 100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4); }
            50% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
        }

        /*========================================
          PRIORITY BADGES
        ========================================*/
        .priority-emergency {
            background: var(--hc-emergency);
            color: white;
        }

        .priority-senior {
            background: #9c27b0;
            color: white;
        }

        .priority-pwd {
            background: var(--hc-info);
            color: white;
        }

        .priority-regular {
            background: var(--hc-text-muted);
            color: white;
        }

        /*========================================
          FORMS
        ========================================*/
        .form-control-healthcare {
            border: 2px solid var(--hc-border);
            border-radius: var(--hc-radius-md);
            padding: 0.875rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: var(--hc-card);
        }

        .form-control-healthcare:focus {
            border-color: var(--hc-primary);
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
            outline: none;
        }

        .form-label {
            font-weight: 600;
            color: var(--hc-text);
            margin-bottom: 0.5rem;
        }

        /* Radio/Checkbox Cards */
        .selection-card {
            border: 2px solid var(--hc-border);
            border-radius: var(--hc-radius-md);
            padding: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            background: var(--hc-card);
        }

        .selection-card:hover {
            border-color: var(--hc-primary);
            background: var(--hc-primary-bg);
            transform: translateY(-2px);
        }

        .selection-card.selected {
            border-color: var(--hc-primary);
            background: var(--hc-primary-bg);
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.2);
        }

        /*========================================
          FOOTER
        ========================================*/
        .footer-healthcare {
            background: var(--hc-gradient-dark);
            color: white;
            padding: 3rem 0 1.5rem;
        }

        .footer-healthcare h5 {
            color: white;
            font-weight: 700;
        }

        .footer-healthcare a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-healthcare a:hover {
            color: white;
        }

        /*========================================
          ANIMATIONS
        ========================================*/
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fadeInUp {
            animation: fadeInUp 0.6s ease forwards;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .animate-float {
            animation: float 3s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .animate-pulse {
            animation: pulse 2s ease-in-out infinite;
        }

        /* Hover Scale for Cards */
        .hover-scale {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .hover-scale:hover {
            transform: translateY(-8px) scale(1.03);
        }

        /* Hover Lift */
        .hover-lift {
            transition: all 0.3s ease;
        }

        .hover-lift:hover {
            transform: translateY(-5px);
            box-shadow: var(--hc-shadow-lg);
        }

        /*========================================
          ALERTS
        ========================================*/
        .alert-healthcare {
            border: none;
            border-radius: var(--hc-radius-md);
            padding: 1rem 1.25rem;
            font-weight: 500;
        }

        .alert-success-hc {
            background: var(--hc-secondary-bg);
            color: #0f5132;
            border-left: 4px solid var(--hc-success);
        }

        .alert-danger-hc {
            background: #f8d7da;
            color: #842029;
            border-left: 4px solid var(--hc-emergency);
        }

        .alert-info-hc {
            background: var(--hc-primary-bg);
            color: #084298;
            border-left: 4px solid var(--hc-primary);
        }

        /*========================================
          SCROLLBAR
        ========================================*/
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--hc-bg);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--hc-primary);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--hc-primary-dark);
        }

        /*========================================
          RESPONSIVE
        ========================================*/
        @media (max-width: 768px) {
            .queue-number-display {
                font-size: 3rem;
            }
            
            .queue-number-large {
                font-size: 4rem;
            }
            
            .stat-card .stat-value {
                font-size: 2rem;
            }
        }
    </style>

    @stack('styles')
</head>
<body>
    <!-- Navigation -->
    @if(!View::hasSection('hideHeader'))
    <nav class="navbar navbar-expand-lg navbar-healthcare sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                <i class="fas fa-hospital-user"></i>
                HealthQueue
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center gap-2">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}">
                            <i class="fas fa-home"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('queue.join') }}">
                            <i class="fas fa-plus-circle"></i> Join Queue
                        </a>
                    </li>
                    <li class="nav-item">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('queue.check') }}">
                            <i class="fas fa-search"></i> Check Status
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('display') }}" target="_blank">
                            <i class="fas fa-tv"></i> Display
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    @endif

    <!-- Flash Messages handled via SweetAlert -->

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    @if(!View::hasSection('hideFooter'))
    <footer class="footer-healthcare mt-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h5 class="mb-3">
                        <i class="fas fa-hospital-user me-2"></i>
                        HealthQueue
                    </h5>
                    <p class="text-white-50 mb-3">
                        Smart Healthcare Queue Management System. 
                        Reduce waiting time. Improve patient experience.
                    </p>
                    <p class="text-white-50 small">
                        <i class="fas fa-shield-alt me-1"></i> HIPAA Compliant
                    </p>
                </div>
                <div class="col-lg-2 col-md-4">
                    <h6 class="fw-bold mb-3">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="{{ route('queue.join') }}"><i class="fas fa-chevron-right me-1 small"></i> Join Queue</a></li>
                        <li class="mb-2"><a href="{{ route('queue.check') }}"><i class="fas fa-chevron-right me-1 small"></i> Check Status</a></li>
                        <li class="mb-2"><a href="{{ route('display') }}"><i class="fas fa-chevron-right me-1 small"></i> Live Display</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-4">
                    <h6 class="fw-bold mb-3">Our Services</h6>
                    <ul class="list-unstyled text-white-50">
                        <li class="mb-2"><i class="fas fa-stethoscope me-2"></i> Consultation</li>
                        <li class="mb-2"><i class="fas fa-flask me-2"></i> Laboratory</li>
                        <li class="mb-2"><i class="fas fa-pills me-2"></i> Pharmacy</li>
                        <li class="mb-2"><i class="fas fa-x-ray me-2"></i> Radiology</li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-4">
                    <h6 class="fw-bold mb-3">Contact Us</h6>
                    <ul class="list-unstyled text-white-50">
                        <li class="mb-2"><i class="fas fa-map-marker-alt me-2"></i> 123 Medical Center Drive</li>
                        <li class="mb-2"><i class="fas fa-phone me-2"></i> +1 (234) 567-8900</li>
                        <li class="mb-2"><i class="fas fa-envelope me-2"></i> info@healthqueue.com</li>
                        <li class="mb-2"><i class="fas fa-clock me-2"></i> Mon-Sun: 24/7</li>
                    </ul>
                </div>
            </div>
            <hr class="my-4 border-secondary opacity-25">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <p class="text-white-50 small mb-0">&copy; {{ date('Y') }} HealthQueue. All rights reserved.</p>
                <div class="d-flex gap-3">
                    <a href="#" class="text-white-50 small" data-bs-toggle="modal" data-bs-target="#privacyModal">Privacy Policy</a>
                    <a href="#" class="text-white-50 small" data-bs-toggle="modal" data-bs-target="#termsModal">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>
    @endif

    <!-- Modals (Global) -->
    <div class="modal fade" id="privacyModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Privacy Policy</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Welcome to HealthQueue ("we," "our," or "us"). We are committed to protecting your personal information and your right to privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our queue management system.</p>
                    
                    <h6 class="fw-bold mt-4">1. Information We Collect</h6>
                    <p class="small text-muted">We collect personal information that you voluntarily provide when you register for the queue service. This includes: Full Name, Phone Number, Email Address, and Health Priority Status (e.g., Senior, PWD).</p>

                    <h6 class="fw-bold mt-3">2. How We Use Your Information</h6>
                    <p class="small text-muted">We use your information solely for the purpose of managing the queue system. This includes: Generating your queue ticket, Sending email notifications about your queue status, and Verifying your identity at the service counter.</p>
                    
                    <h6 class="fw-bold mt-3">3. Data Security</h6>
                    <p class="small text-muted">We use administrative, technical, and physical security measures to help protect your personal information.</p>
                    
                    <h6 class="fw-bold mt-3">4. Contact Us</h6>
                    <p class="small text-muted">If you have questions or comments about this policy, you may email us at info@healthqueue.com.</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Understood</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="termsModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Terms of Service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Please read these Terms of Service ("Terms") carefully before using the HealthQueue system.</p>
                    
                    <h6 class="fw-bold mt-4">1. Acceptance of Terms</h6>
                    <p class="small text-muted">By accessing or using our Service, you agree to be bound by these Terms. If you disagree with any part of the terms, then you may not use the Service.</p>

                    <h6 class="fw-bold mt-3">2. Use of the System</h6>
                    <p class="small text-muted">The HealthQueue system is intended for legitimate patient queuing purposes only. misuse, abusive behavior, or attempt to manipulate the queue system is strictly prohibited.</p>
                    
                    <h6 class="fw-bold mt-3">3. Accuracy of Information</h6>
                    <p class="small text-muted">You agree to provide accurate, current, and complete information during the registration process. False information regarding priority status (e.g., claiming Senior Citizen status without proof) may result in denial of priority service.</p>
                    
                    <h6 class="fw-bold mt-3">4. Limitation of Liability</h6>
                    <p class="small text-muted">HealthQueue is not liable for any delays in medical service or medical outcomes resulting from the use of the queue system.</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">I Agree</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- CSRF Token for AJAX -->
    <script>
        window.csrfToken = '{{ csrf_token() }}';

        @if(session('success') || session('swal_success'))
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: "{{ session('success') ?? session('swal_success') }}",
                confirmButtonColor: '#0D6EFD',
                confirmButtonText: 'OK'
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: "{{ session('error') }}",
                confirmButtonColor: '#DC3545',
                confirmButtonText: 'Dismiss'
            });
        @endif
        
        // Hide navbar on scroll down, show on scroll up
        (function() {
            let lastScrollTop = 0;
            const navbar = document.querySelector('.navbar-healthcare');
            if (!navbar) return;
            const scrollThreshold = 50;
            
            window.addEventListener('scroll', function() {
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                
                if (scrollTop > lastScrollTop && scrollTop > scrollThreshold) {
                    // Scrolling down
                    navbar.classList.add('navbar-hidden');
                } else {
                    // Scrolling up
                    navbar.classList.remove('navbar-hidden');
                }
                
                lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
            }, false);
        })();

        // Persist Admin Sidebar Scroll
        document.addEventListener("DOMContentLoaded", function () {
            const sidebar = document.querySelector('.admin-sidebar');
            // Or .sidebar-content if used in admin
            // Based on sidebar.blade.php it is .admin-sidebar
            if (sidebar) {
                 const pos = localStorage.getItem('admin-sidebar-scroll');
                 if (pos) sidebar.scrollTop = pos;
                 sidebar.addEventListener('scroll', function() {
                     localStorage.setItem('admin-sidebar-scroll', sidebar.scrollTop);
                 });
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
