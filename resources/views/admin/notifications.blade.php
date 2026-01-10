@extends('layouts.admin')

@section('title', 'Notifications - Smart Healthcare')

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Notifications</h4>
        <p class="text-muted small mb-0">History of System Notifications</p>
    </div>
</div>

<!-- Notifications Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center" style="font-size: 0.8rem;">
                <thead class="bg-light">
                    <tr>
                        <th class="border-bottom-0">Date</th>
                        <th class="border-bottom-0">Recipient</th>
                        <th class="border-bottom-0">Message</th>
                        <th class="border-bottom-0">Type</th>
                        <th class="border-bottom-0">Queue #</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td>
                            <div>{{ $log->created_at->format('M d, Y') }}</div>
                            <small class="text-muted">{{ $log->created_at->format('h:i A') }}</small>
                        </td>
                        <td>
                            @if($log->notifiable)
                                <div class="fw-medium">{{ $log->notifiable->full_name ?? $log->notifiable->name ?? 'Unknown' }}</div>
                                <small class="text-muted">{{ class_basename($log->notifiable_type) }}</small>
                            @else
                                <span class="text-muted">Unknown</span>
                            @endif
                        </td>
                        <td class="text-start">
                            <div class="d-flex align-items-center">
                                @if(isset($log->data['icon']))
                                    <i class="{{ $log->data['icon'] }} me-2 text-{{ $log->data['color'] ?? 'secondary' }}"></i>
                                @endif
                                <div>
                                    <span class="d-block fw-bold">{{ $log->data['title'] ?? 'Notification' }}</span>
                                    <small class="text-muted">{{ $log->data['message'] ?? '' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ ucfirst($log->data['type'] ?? 'General') }}</span>
                        </td>
                        <td>
                             <span class="fw-bold">{{ $log->data['queue_number'] ?? '-' }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="fas fa-bell-slash fa-2x mb-2 opacity-25"></i>
                            <p class="mb-0 small">No notifications found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($logs->hasPages())
    <div class="card-footer bg-white py-2 d-flex justify-content-end">
        {{ $logs->links() }}
    </div>
    @endif
</div>
@endsection
