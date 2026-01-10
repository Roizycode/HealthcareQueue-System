<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard - Smart Healthcare')</title>
    <link rel="icon" type="image/png" href="{{ asset('image/Iconlogo.png') }}">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 280px;
            --sidebar-bg: #0f172a;
            --sidebar-hover: #1e293b;
            --sidebar-active: #334155;
            --sidebar-text: #94a3b8;
            --sidebar-text-active: #ffffff;
            --sidebar-border: #1e293b;
            --accent-primary: #3b82f6;
            --accent-success: #10b981;
            --accent-warning: #f59e0b;
            --accent-danger: #ef4444;
            
            /* Healthcare Gradients */
            --hc-primary: #0D6EFD;
            --hc-secondary: #20C997;
            --hc-gradient-primary: linear-gradient(135deg, #0D6EFD 0%, #0dcaf0 100%);
            --hc-gradient-secondary: linear-gradient(135deg, #20C997 0%, #198754 100%);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f9;
            overflow-x: hidden;
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
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 1000;
            overflow-y: auto;
            scrollbar-width: none;
        }

        #sidebar-wrapper::-webkit-scrollbar {
            display: none;
        }

        .sidebar-brand {
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-bottom: 1px solid var(--sidebar-border);
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        }

        .sidebar-brand-icon {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, var(--accent-primary) 0%, #60a5fa 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem;
        }

        .sidebar-brand-text {
            color: white;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .sidebar-brand-text small {
            display: block;
            font-size: 0.65rem;
            font-weight: 500;
            color: var(--sidebar-text);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .sidebar-content {
            flex-grow: 1;
            padding: 1rem 0;
        }

        .nav-section-label {
            padding: 1.25rem 1.5rem 0.5rem;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #475569;
        }

        .nav-link {
            padding: 0.7rem 1.5rem;
            color: var(--sidebar-text);
            font-weight: 500;
            font-size: 0.9rem;
            border-radius: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
        }

        .nav-link:hover {
            color: var(--sidebar-text-active);
            background-color: var(--sidebar-hover);
            border-left-color: var(--accent-primary);
        }

        .nav-link.active {
            color: var(--sidebar-text-active);
            background-color: var(--sidebar-active);
            border-left-color: var(--accent-primary);
            font-weight: 600;
        }

        .nav-link i {
            width: 20px;
            text-align: center;
            font-size: 1rem;
            opacity: 0.8;
        }

        .nav-link.active i,
        .nav-link:hover i {
            opacity: 1;
        }

        .nav-badge {
            margin-left: auto;
            font-size: 0.7rem;
            padding: 0.2rem 0.5rem;
            border-radius: 50px;
        }

        /* Submenu */
        .nav-submenu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            background: rgba(0,0,0,0.15);
        }

        .nav-submenu.show {
            max-height: 500px;
        }

        .nav-submenu .nav-link {
            padding-left: 3.25rem;
            font-size: 0.85rem;
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }

        .nav-toggle {
            cursor: pointer;
        }

        .nav-toggle::after {
            content: '\f078';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            margin-left: auto;
            font-size: 0.65rem;
            transition: transform 0.3s ease;
            opacity: 0.5;
        }

        .nav-toggle.collapsed::after {
            transform: rotate(-90deg);
        }

        /* Page Content */
        #page-content-wrapper {
            width: 100%;
            margin-left: var(--sidebar-width);
            padding: 1.5rem 2rem;
            min-height: 100vh;
        }

        /* Top Header Bar */
        .top-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .page-title {
            font-weight: 700;
            color: #0f172a;
            font-size: 1.5rem;
            margin: 0;
        }

        .page-subtitle {
            color: #64748b;
            font-size: 0.85rem;
            margin: 0;
        }

        /* Sidebar Footer */
        .sidebar-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--sidebar-border);
            background: rgba(0,0,0,0.2);
        }

        .admin-profile {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 0;
        }

        .admin-avatar {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--accent-primary) 0%, #60a5fa 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .admin-info {
            flex: 1;
        }

        .admin-name {
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
            line-height: 1.2;
        }

        .admin-role {
            color: var(--sidebar-text);
            font-size: 0.75rem;
        }

        .logout-btn {
            background: transparent;
            border: 1px solid #475569;
            color: var(--sidebar-text);
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            font-size: 0.8rem;
            transition: all 0.2s;
        }

        .logout-btn:hover {
            background: var(--accent-danger);
            border-color: var(--accent-danger);
            color: white;
        }

        /* Utilities */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        .card {
            border: none;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            border-radius: 12px;
        }

        .card-header {
            background: white;
            border-bottom: 1px solid #e2e8f0;
            font-weight: 600;
        }

        /* Responsive */
        @media (max-width: 992px) {
            #sidebar-wrapper {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            #sidebar-wrapper.show {
                transform: translateX(0);
            }

            #page-content-wrapper {
                margin-left: 0;
            }
        }
        
        /* Scrollbar Styling - Hidden as per user request */
        ::-webkit-scrollbar {
            display: none;
        }
        html {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        
        /* Prevent Layout Shift */
        body { padding-right: 0 !important; }
        body.modal-open, body.swal2-shown {
            padding-right: 0 !important;
            overflow-y: scroll !important; /* Allow scroll but hidden */
        }
    </style>
    @stack('styles')
</head>
<body>
    <div id="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar-wrapper">
            <div class="sidebar-brand">
                <div class="sidebar-brand-icon text-primary">
                    <i class="fas fa-hospital"></i>
                </div>
                <div class="sidebar-brand-text">
                    Smart Healthcare
                    <small>Admin Panel</small>
                </div>
                <button class="btn btn-link text-dark d-lg-none p-0 ms-auto" id="internal-sidebar-toggle" style="font-size: 1.2rem;">
                    <i class="fas fa-bars"></i>
                </button>
            </div>

            <div class="sidebar-content">
                <!-- Dashboard -->
                <div class="nav-section-label">Overview</div>
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-th-large"></i>
                    <span>Dashboard</span>
                </a>

                <!-- Patients -->
                <div class="nav-section-label">Patient Management</div>
                <a href="{{ route('admin.patients') }}" class="nav-link {{ request()->routeIs('admin.patients') ? 'active' : '' }}">
                    <i class="fas fa-users"></i>
                    <span>Patients</span>
                </a>
                <a href="{{ route('admin.patients.add') }}" class="nav-link {{ request()->routeIs('admin.patients.add') ? 'active' : '' }}">
                    <i class="fas fa-user-plus"></i>
                    <span>Add Patient</span>
                </a>

                <!-- Staff Management -->
                <div class="nav-section-label">Staff Management</div>
                <a href="{{ route('admin.staff.index') }}" class="nav-link {{ request()->routeIs('admin.staff.*') ? 'active' : '' }}">
                    <i class="fas fa-user-md"></i>
                    <span>Staff Accounts</span>
                </a>

                <!-- Services -->
                <div class="nav-section-label">Services</div>
                <a href="{{ route('admin.settings') }}#services" class="nav-link {{ request()->routeIs('admin.services.*') ? 'active' : '' }}">
                    <i class="fas fa-concierge-bell"></i>
                    <span>Manage Services</span>
                </a>



                <!-- Queue Management -->
                <div class="nav-section-label">Queue Management</div>
                <a href="{{ route('admin.queue.live') }}" class="nav-link {{ request()->routeIs('admin.queue.live') ? 'active' : '' }}">
                    <i class="fas fa-tv"></i>
                    <span>Live Monitoring</span>
                    <span class="nav-badge bg-success text-white">LIVE</span>
                </a>
                <a href="{{ route('admin.queue.priority') }}" class="nav-link {{ request()->routeIs('admin.queue.priority') ? 'active' : '' }}">
                    <i class="fas fa-layer-group"></i>
                    <span>Priority Queue</span>
                </a>
                <a href="{{ route('admin.queue.virtual') }}" class="nav-link {{ request()->routeIs('admin.queue.virtual') ? 'active' : '' }}">
                    <i class="fas fa-mobile-alt"></i>
                    <span>Virtual Queue</span>
                </a>
                <a href="{{ route('admin.queue.live') }}" class="nav-link {{ request()->routeIs('admin.queue.live') ? 'active' : '' }}">
                    <i class="fas fa-desktop"></i>
                    <span>Display Screen</span>
                </a>

                <!-- Queue Settings -->
                <div class="nav-section-label">Queue Settings</div>
                <a href="{{ route('admin.queue-settings') }}" class="nav-link {{ request()->routeIs('admin.queue-settings') ? 'active' : '' }}">
                    <i class="fas fa-sliders-h"></i>
                    <span>Queue Configuration</span>
                </a>

                <!-- Reports -->
                <div class="nav-section-label">Reports & Analytics</div>
                <a href="{{ route('admin.analytics') }}" class="nav-link {{ request()->routeIs('admin.analytics') ? 'active' : '' }}">
                    <i class="fas fa-chart-line"></i>
                    <span>Analytics</span>
                </a>
                <a href="{{ route('admin.reports') }}" class="nav-link {{ request()->routeIs('admin.reports') ? 'active' : '' }}">
                    <i class="fas fa-file-alt"></i>
                    <span>Reports</span>
                </a>

                <!-- Payments -->
                <div class="nav-section-label">Payments</div>
                <a href="{{ route('admin.payments') }}" class="nav-link {{ request()->routeIs('admin.payments') ? 'active' : '' }}">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Payments</span>
                </a>
                <a href="{{ route('admin.transactions') }}" class="nav-link {{ request()->routeIs('admin.transactions') ? 'active' : '' }}">
                    <i class="fas fa-exchange-alt"></i>
                    <span>Transactions</span>
                </a>
                <a href="{{ route('admin.receipts') }}" class="nav-link {{ request()->routeIs('admin.receipts') ? 'active' : '' }}">
                    <i class="fas fa-receipt"></i>
                    <span>Receipts</span>
                </a>

                <!-- Settings -->
                <div class="nav-section-label">System</div>
                <a href="{{ route('admin.settings') }}" class="nav-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
                <a href="{{ route('admin.notifications') }}" class="nav-link {{ request()->routeIs('admin.notifications') ? 'active' : '' }}">
                    <i class="fas fa-bell"></i>
                    <span>Notifications</span>
                </a>
                <a href="{{ route('admin.audit-logs') }}" class="nav-link {{ request()->routeIs('admin.audit-logs') ? 'active' : '' }}">
                    <i class="fas fa-history"></i>
                    <span>Audit Logs</span>
                </a>
            </div>

            <!-- Sidebar Footer -->
            <div class="sidebar-footer">
                <div class="admin-profile">
                    <div class="admin-avatar">
                        {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                    </div>
                    <div class="admin-info">
                        <div class="admin-name">{{ auth()->user()->name ?? 'Admin User' }}</div>
                        <div class="admin-role">Administrator</div>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="mt-2">
                    @csrf
                    <button type="submit" class="logout-btn w-100">
                        <i class="fas fa-sign-out-alt me-1"></i> Logout
                    </button>
                </form>
            </div>
        </nav>

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <!-- Mobile Toggle (Non-Floating) -->
            <button class="btn btn-link text-dark d-lg-none m-3 p-0" id="mobile-sidebar-toggle" style="font-size: 1.5rem; text-decoration: none;">
                <i class="fas fa-bars"></i>
            </button>
            @yield('content')
        </div>
    </div>
    
    <!-- Mobile Toggle (Icon Only) -->
    <!-- Sidebar Overlay -->
    <div id="sidebar-overlay" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 d-none d-lg-none" style="z-index: 999; backdrop-filter: blur(2px);"></div>
    
    @stack('modals')

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
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
        
        // Toggle Submenus
        document.querySelectorAll('.nav-toggle').forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                const submenu = this.nextElementSibling;
                if (submenu && submenu.classList.contains('nav-submenu')) {
                    submenu.classList.toggle('show');
                    this.classList.toggle('collapsed');
                }
            });
        });

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
            
            // Restore Sidebar Scroll Persistence
            const sidebarContent = document.querySelector('#sidebar-wrapper');
            const mainContent = document.querySelector('#page-content-wrapper');
            
            // Disable browser auto scroll restoration
            if ('scrollRestoration' in history) {
                history.scrollRestoration = 'manual';
            }
            
            // Restore scroll position on page load
            const savedScrollPos = sessionStorage.getItem('adminScrollPos');
            if (savedScrollPos && mainContent) {
                mainContent.scrollTop = parseInt(savedScrollPos);
            }
            
            // Save scroll position when clicking sidebar links
            document.querySelectorAll('#sidebar-wrapper a').forEach(link => {
                link.addEventListener('click', function() {
                    if (mainContent) {
                        sessionStorage.setItem('adminScrollPos', mainContent.scrollTop);
                    }
                });
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
