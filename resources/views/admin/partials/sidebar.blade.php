<!-- Admin Sidebar -->
<div class="col-lg-2 admin-sidebar d-none d-lg-block">
    <div class="p-3">
        <div class="px-3 py-3 mb-3">
            <h6 class="text-white-50 text-uppercase small fw-bold mb-0">
                <i class="fas fa-chart-line me-2"></i>Admin Panel
            </h6>
        </div>
        
        <nav class="nav flex-column">
            <a class="nav-link {{ ($active ?? '') == 'dashboard' ? 'active' : '' }}" href="/admin">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            
            <div class="px-3 py-2 mt-3">
                <span class="text-white-50 text-uppercase small fw-bold">Queue Management</span>
            </div>
            <a class="nav-link {{ ($active ?? '') == 'live-queue' ? 'active' : '' }}" href="/admin/queue/live">
                <i class="fas fa-broadcast-tower"></i> Live Queue
            </a>
            <a class="nav-link {{ ($active ?? '') == 'priority-queue' ? 'active' : '' }}" href="/admin/queue/priority">
                <i class="fas fa-sort-amount-up"></i> Priority Queue
            </a>
            <a class="nav-link {{ ($active ?? '') == 'virtual-queue' ? 'active' : '' }}" href="/admin/queue/virtual">
                <i class="fas fa-mobile-alt"></i> Virtual Queue
            </a>
            
            <div class="px-3 py-2 mt-3">
                <span class="text-white-50 text-uppercase small fw-bold">Patients</span>
            </div>
            <a class="nav-link {{ ($active ?? '') == 'patients' ? 'active' : '' }}" href="/admin/patients">
                <i class="fas fa-users"></i> All Patients
            </a>
            <a class="nav-link {{ ($active ?? '') == 'add-patient' ? 'active' : '' }}" href="/admin/patients/add">
                <i class="fas fa-user-plus"></i> Add Patient
            </a>
            
            <div class="px-3 py-2 mt-3">
                <span class="text-white-50 text-uppercase small fw-bold">Services</span>
            </div>
            @php 
                try {
                    $sidebarServices = \App\Models\Service::active()->ordered()->get(); 
                } catch (\Exception $e) {
                    $sidebarServices = collect([]);
                }
            @endphp
            @foreach($sidebarServices as $service)
            <a class="nav-link {{ ($active ?? '') == 'service-'.$service->id ? 'active' : '' }}" href="/admin/service/{{ $service->id }}">
                <span class="service-badge" style="background: {{ $service->color }};"></span>
                {{ $service->name }}
            </a>
            @endforeach
            
            <div class="px-3 py-2 mt-3">
                <span class="text-white-50 text-uppercase small fw-bold">System</span>
            </div>
            <a class="nav-link {{ ($active ?? '') == 'notifications' ? 'active' : '' }}" href="/admin/notifications">
                <i class="fas fa-bell"></i> Notifications
            </a>
            <a class="nav-link {{ ($active ?? '') == 'audit-logs' ? 'active' : '' }}" href="/admin/audit-logs">
                <i class="fas fa-clipboard-list"></i> Audit Logs
            </a>
            <a class="nav-link {{ ($active ?? '') == 'reports' ? 'active' : '' }}" href="/admin/reports">
                <i class="fas fa-file-alt"></i> Reports
            </a>
            <a class="nav-link {{ ($active ?? '') == 'transactions' ? 'active' : '' }}" href="/admin/transactions">
                <i class="fas fa-exchange-alt"></i> Transactions
            </a>
            <a class="nav-link {{ ($active ?? '') == 'receipts' ? 'active' : '' }}" href="/admin/receipts">
                <i class="fas fa-receipt"></i> Receipts
            </a>
            <a class="nav-link {{ ($active ?? '') == 'analytics' ? 'active' : '' }}" href="/admin/analytics">
                <i class="fas fa-chart-bar"></i> Analytics
            </a>
            <a class="nav-link {{ ($active ?? '') == 'staff' ? 'active' : '' }}" href="{{ route('admin.staff.index') }}">
                <i class="fas fa-user-md"></i> Staff Management
            </a>
            <a class="nav-link {{ ($active ?? '') == 'queue-settings' ? 'active' : '' }}" href="/admin/queue-settings">
                <i class="fas fa-sliders-h"></i> Queue Settings
            </a>
            <a class="nav-link {{ ($active ?? '') == 'settings' ? 'active' : '' }}" href="/admin/settings">
                <i class="fas fa-cog"></i> Settings
            </a>
        </nav>
    </div>
</div>
