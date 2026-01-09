<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Staff Dashboard')</title>
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

        /* Sidebar Variables */
        :root {
            --sidebar-width: 260px;
            --sidebar-bg: #ffffff;
            --sidebar-border: #e9ecef;
            --sidebar-text: #495057;
            --sidebar-active-bg: #e7f1ff;
            --sidebar-active-text: #0d6efd;
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

        .nav-submenu .nav-link {
            padding-left: 3.5rem;
            font-size: 0.95em;
        }

        /* Page Content */
        #page-content-wrapper {
            width: 100%;
            margin-left: var(--sidebar-width);
            padding: 2rem;
            min-height: 100vh;
        }

        /* User Profile in Sidebar Bottom */
        .sidebar-footer {
            padding: 1rem;
            border-top: 1px solid var(--sidebar-border);
        }

        /* Utilities */
        /* Hide scrollbar globally but allow scrolling */
        ::-webkit-scrollbar {
            display: none;
        }
        .bg-purple {
            background-color: #6f42c1 !important;
            color: #ffffff !important;
        }
        * {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .letter-spacing-1 { letter-spacing: 1px; }
        .divide-x > * + * { border-left: 1px solid #dee2e6; }
    </style>
    @stack('styles')
</head>
<body>
    <div id="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar-wrapper">
            <div class="sidebar-brand">
                <i class="fas fa-hospital-user text-primary"></i>
                <span>HealthQueue</span>
            </div>

            <div class="sidebar-content">
                <!-- Main -->
                <div class="nav-section-label pt-2">Main</div>
                <a href="{{ route('staff.dashboard') }}" class="nav-link {{ request()->routeIs('staff.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-th-large"></i> 
                    <span>Dashboard</span>
                </a>

                <!-- Queue Management -->
                <div class="nav-section-label">Queues</div>
                <a href="{{ route('display') }}" target="_blank" class="nav-link">
                    <i class="fas fa-desktop"></i>
                    <span>Live Display</span>
                </a>
                <a href="/staff/queue/priority" class="nav-link {{ request()->is('staff/queue/priority') ? 'active' : '' }}">
                    <i class="fas fa-layer-group"></i>
                    <span>Priority Queue</span>
                </a>

                <!-- Patient Records -->
                <div class="nav-section-label">Patients</div>
                <a href="/staff/patients" class="nav-link {{ request()->is('staff/patients') ? 'active' : '' }}">
                    <i class="fas fa-users"></i>
                    <span>All Patients</span>
                </a>
                <a href="/staff/patients/add" class="nav-link {{ request()->is('staff/patients/add') ? 'active' : '' }}">
                    <i class="fas fa-user-plus"></i>
                    <span>Add Patient</span>
                </a>

                <!-- Services -->
                <div class="nav-section-label">Services</div>
                @php $services = \App\Models\Service::active()->ordered()->get(); @endphp
                @foreach($services as $svc)
                <a href="/staff/service/{{ $svc->id }}" class="nav-link {{ request()->is('staff/service/'.$svc->id) ? 'active' : '' }}">
                    <span class="rounded-circle d-inline-block border" style="width: 10px; height: 10px; background-color: {{ $svc->color }}; margin-left: 5px; margin-right: 5px;"></span>
                    <span>{{ $svc->name }}</span>
                </a>
                @endforeach

                <!-- System -->
                <div class="nav-section-label">System</div>
                <a href="/staff/notifications" class="nav-link {{ request()->is('staff/notifications') ? 'active' : '' }}">
                    <i class="fas fa-bell"></i>
                    <span>Notifications</span>
                </a>
                <a href="/staff/reports" class="nav-link {{ request()->is('staff/reports') ? 'active' : '' }}">
                    <i class="fas fa-chart-pie"></i>
                    <span>Reports</span>
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
             @yield('content')
        </div>
    </div>
    
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
        
        // Persist Sidebar Scroll Position
        document.addEventListener("DOMContentLoaded", function () {
            const sidebar = document.querySelector('.sidebar-content');
            if (sidebar) {
                const pos = localStorage.getItem('sidebar-scroll-pos');
                if (pos) sidebar.scrollTop = pos;
                
                sidebar.addEventListener('scroll', function() {
                    localStorage.setItem('sidebar-scroll-pos', sidebar.scrollTop);
                });
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
