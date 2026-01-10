<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Patient Dashboard - Smart Healthcare')</title>
    <link rel="icon" type="image/png" href="{{ asset('image/Iconlogo.png') }}">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8f9fa;
        }

        /* Sidebar Variables - Patient Theme (Green/Teal) */
        :root {
            --sidebar-width: 260px;
            --sidebar-bg: #ffffff;
            --sidebar-border: #e9ecef;
            --sidebar-text: #495057;
            --sidebar-active-bg: #d1fae5;
            --sidebar-active-text: #059669;
            --patient-primary: #20C997;
            --patient-primary-dark: #059669;
        }

        #wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        /* Sidebar Styling */
        #sidebar-wrapper {
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            border-right: 1px solid var(--sidebar-border);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 1000;
        }

        .sidebar-brand {
            padding: 1.5rem;
            font-size: 1.25rem;
            font-weight: 700;
            color: #212529;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-bottom: 1px solid var(--sidebar-border);
        }

        .sidebar-brand i {
            color: var(--patient-primary);
        }

        .sidebar-content {
            flex-grow: 1;
            overflow-y: auto;
            padding: 1rem 0;
        }

        .nav-section-label {
            padding: 1.5rem 1.5rem 0.5rem;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #adb5bd;
        }

        .nav-link {
            padding: 0.75rem 1.5rem;
            color: var(--sidebar-text);
            font-weight: 500;
            border-radius: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.2s;
        }

        .nav-link:hover {
            color: var(--sidebar-active-text);
            background-color: #f8f9fa;
        }

        .nav-link.active {
            color: var(--sidebar-active-text);
            background-color: var(--sidebar-active-bg);
            font-weight: 600;
            border-right: 3px solid var(--sidebar-active-text);
        }

        .nav-link i {
            width: 20px;
            text-align: center;
            font-size: 1.1em;
        }

        /* Page Content */
        #page-content-wrapper {
            width: 100%;
            margin-left: var(--sidebar-width);
            padding: 2rem;
            min-height: 100vh;
        }

        /* User Profile in Sidebar */
        .sidebar-user {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--sidebar-border);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .sidebar-user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--patient-primary) 0%, #0dcaf0 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .sidebar-user-info {
            flex: 1;
            min-width: 0;
        }

        .sidebar-user-name {
            font-weight: 600;
            font-size: 0.9rem;
            color: #212529;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-user-role {
            font-size: 0.75rem;
            color: #6c757d;
        }

        /* Sidebar Footer */
        .sidebar-footer {
            padding: 1rem;
            border-top: 1px solid var(--sidebar-border);
        }

        /* Utilities */
        ::-webkit-scrollbar {
            display: none;
        }
        * {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* Patient Card Styling */
        .patient-card {
            background: white;
            border-radius: 12px;
            border: 1px solid #e9ecef;
            transition: all 0.2s;
        }

        .patient-card:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        /* Responsive */
        @media (max-width: 991px) {
            #sidebar-wrapper {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            #sidebar-wrapper.show {
                transform: translateX(0);
            }

            #page-content-wrapper {
                margin-left: 0;
                padding: 1rem;
            }
        }

        /* Prevent Layout Shift - Robust Fix */
        html { overflow-y: scroll !important; }
        body { padding-right: 0 !important; }
        body.modal-open, body.swal2-shown {
            padding-right: 0 !important;
            overflow-y: scroll !important;
        }
    </style>
    @stack('styles')
</head>
<body>
    <div id="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar-wrapper">
            <div class="sidebar-brand">
                <i class="fas fa-hospital-user text-primary"></i>
                <span>Smart Healthcare</span>
                <button class="btn btn-link text-dark d-lg-none p-0 ms-auto" id="internal-sidebar-toggle" style="font-size: 1.2rem;">
                    <i class="fas fa-bars"></i>
                </button>
            </div>

            <div class="sidebar-content">
                <!-- Main -->
                <div class="nav-section-label pt-2">Main</div>
                <a href="{{ route('patient.dashboard') }}" class="nav-link {{ request()->routeIs('patient.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-th-large"></i> 
                    <span>Dashboard</span>
                </a>

                <!-- Appointments -->
                <div class="nav-section-label">Appointments</div>
                <a href="{{ route('patient.request-appointment') }}" class="nav-link {{ request()->routeIs('patient.request-appointment') ? 'active' : '' }}">
                    <i class="fas fa-calendar-plus"></i>
                    <span>Request Appointment</span>
                </a>
                <a href="{{ route('patient.my-requests') }}" class="nav-link {{ request()->routeIs('patient.my-requests') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-list"></i>
                    <span>My Requests</span>
                </a>
                <a href="{{ route('patient.appointments') }}" class="nav-link {{ request()->routeIs('patient.appointments') ? 'active' : '' }}">
                    <i class="fas fa-history"></i>
                    <span>Appointment History</span>
                </a>

                <!-- Queue -->
                <div class="nav-section-label">Queue</div>
                <a href="{{ route('patient.queue.join') }}" class="nav-link {{ request()->routeIs('patient.queue.join') ? 'active' : '' }}">
                    <i class="fas fa-ticket-alt"></i>
                    <span>Get Queue Number</span>
                </a>
                <a href="{{ route('patient.queue-check') }}" class="nav-link {{ request()->routeIs('patient.queue-check') ? 'active' : '' }}">
                    <i class="fas fa-search-location"></i>
                    <span>Check Queue Status</span>
                </a>
                <a href="{{ route('patient.live-display') }}" class="nav-link {{ request()->routeIs('patient.live-display') ? 'active' : '' }}">
                    <i class="fas fa-tv"></i>
                    <span>Live Display</span>
                </a>

                <!-- Account -->
                <div class="nav-section-label">Account</div>
                <a href="{{ route('patient.profile') }}" class="nav-link {{ request()->routeIs('patient.profile') ? 'active' : '' }}">
                    <i class="fas fa-user-cog"></i>
                    <span>My Profile</span>
                </a>
            </div>

            <div class="sidebar-footer">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger w-100 btn-sm">
                        <i class="fas fa-sign-out-alt me-1"></i> Logout
                    </button>
                </form>
            </div>
        </nav>

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <!-- Mobile Toggle (Non-Floating) -->
            <button class="btn btn-link text-dark d-lg-none mb-3 p-0" id="mobile-sidebar-toggle" style="font-size: 1.5rem; text-decoration: none;">
                <i class="fas fa-bars"></i>
            </button>
             @yield('content')
        </div>
    </div>
    
    <!-- Sidebar Overlay -->
    <div id="sidebar-overlay" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 d-none d-lg-none" style="z-index: 999; backdrop-filter: blur(2px);"></div>
    
    @stack('modals')

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        window.csrfToken = '{{ csrf_token() }}';

        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: "{{ session('success') }}",
                confirmButtonColor: '#20C997',
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
        
        // Sidebar Toggle Logic
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar-wrapper');
            const toggles = document.querySelectorAll('#mobile-sidebar-toggle, #internal-sidebar-toggle');
            const overlay = document.getElementById('sidebar-overlay');
            
            function toggleSidebar() {
                sidebar.classList.toggle('show');
                overlay.classList.toggle('d-none');
                
                const extBtn = document.getElementById('mobile-sidebar-toggle');
                if(extBtn) {
                    if(sidebar.classList.contains('show')) {
                        extBtn.style.visibility = 'hidden';
                    } else {
                        extBtn.style.visibility = 'visible';
                    }
                }
            }
            
            toggles.forEach(btn => {
                if(btn) btn.addEventListener('click', toggleSidebar);
            });
            
            if(overlay) {
                overlay.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    this.classList.add('d-none');
                });
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
