@extends('layouts.staff')

@section('title', 'Notifications - Smart Healthcare Staff')

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h4 class="fw-bold text-dark mb-1">Notifications</h4>
        <p class="text-muted small mb-0">System alerts and messages</p>
    </div>
    <span class="badge bg-danger rounded-pill" id="unreadCountBadge" style="display: {{ auth()->user()->unreadNotifications()->count() > 0 ? 'inline-block' : 'none' }};">
        {{ auth()->user()->unreadNotifications()->count() }} New
    </span>
</div>

<div class="card border-0 shadow-sm">
    <div class="list-group list-group-flush" id="notificationList">
        @forelse($notifications as $notification)
            <div class="list-group-item p-4 {{ $notification->read_at ? '' : 'bg-light' }}">
                <div class="d-flex align-items-start">
                    <div class="me-3 mt-1">
                        <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="{{ $notification->data['icon'] ?? 'fas fa-bell' }} text-primary"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between mb-1">
                            <h6 class="fw-bold mb-0 text-dark">{{ $notification->data['title'] ?? 'Notification' }}</h6>
                            <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                        </div>
                        <p class="text-muted mb-0 small">{{ $notification->data['message'] ?? '' }}</p>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-5" id="emptyState">
                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                    <i class="fas fa-bell text-secondary fa-2x"></i>
                </div>
                <h5 class="fw-bold text-dark">No new notifications</h5>
                <p class="text-muted small">You're all caught up!</p>
            </div>
        @endforelse
    </div>
    
    @if($notifications->hasPages())
        <div class="card-footer bg-white border-top-0 py-3">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Poll for notifications every 10 seconds
        setInterval(fetchNotifications, 10000);
    });

    function fetchNotifications() {
        fetch('{{ route('staff.notifications.fetch') }}')
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    updateNotificationList(data.notifications);
                    updateUnreadCount(data.unread_count);
                }
            })
            .catch(console.error);
    }

    function updateUnreadCount(count) {
        const badge = document.getElementById('unreadCountBadge');
        if(count > 0) {
            badge.style.display = 'inline-block';
            badge.textContent = count + ' New';
        } else {
            badge.style.display = 'none';
        }
    }

    function updateNotificationList(notifications) {
        if(notifications.length === 0) return; // Keep existing or show empty state if forced

        const container = document.getElementById('notificationList');
        // Simple strategy: If empty state exists, remove it. 
        // Then prepend new ones? No, replacing list for simplicity in this context 
        // or rebuilding it is better than complex diffing for now.
        // But preventing flicker is good.
        
        // For a true "real-time" feel, we'd prepend. 
        // But simply replacing the innerHTML is robust for "polling status".
        
        let html = '';
        if(notifications.length === 0) {
            html = `
            <div class="text-center py-5" id="emptyState">
                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                    <i class="fas fa-bell text-secondary fa-2x"></i>
                </div>
                <h5 class="fw-bold text-dark">No new notifications</h5>
                <p class="text-muted small">You're all caught up!</p>
            </div>`;
        } else {
            notifications.forEach(n => {
                const data = n.data || {};
                const icon = data.icon || 'fas fa-bell';
                const created = new Date(n.created_at).toLocaleString(); // Simple formatting
                const isRead = n.read_at !== null;
                
                html += `
                <div class="list-group-item p-4 ${isRead ? '' : 'bg-light'}">
                    <div class="d-flex align-items-start">
                        <div class="me-3 mt-1">
                            <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="${icon} text-primary"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between mb-1">
                                <h6 class="fw-bold mb-0 text-dark">${data.title || 'Notification'}</h6>
                                <small class="text-muted">Just now</small>
                            </div>
                            <p class="text-muted mb-0 small">${data.message || ''}</p>
                        </div>
                    </div>
                </div>`;
            });
        }
        
        container.innerHTML = html;
    }
</script>
@endpush
