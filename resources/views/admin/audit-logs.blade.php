@extends('layouts.admin')

@section('title', 'Audit Logs - Smart Healthcare')

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Audit Logs</h4>
        <p class="text-muted small mb-0">System activity and user action history</p>
    </div>
    <form action="{{ route('admin.audit-logs') }}" method="GET" class="d-flex gap-2">
        <select name="action" class="form-select form-select-sm" style="width: auto;">
            <option value="">All Actions</option>
            <option value="login" {{ request('action') === 'login' ? 'selected' : '' }}>Login</option>
            <option value="logout" {{ request('action') === 'logout' ? 'selected' : '' }}>Logout</option>
            <option value="create" {{ request('action') === 'create' ? 'selected' : '' }}>Create</option>
            <option value="update" {{ request('action') === 'update' ? 'selected' : '' }}>Update</option>
            <option value="delete" {{ request('action') === 'delete' ? 'selected' : '' }}>Delete</option>
            <option value="queue_call" {{ request('action') === 'queue_call' ? 'selected' : '' }}>Queue Call</option>
            <option value="queue_complete" {{ request('action') === 'queue_complete' ? 'selected' : '' }}>Queue Complete</option>
        </select>
        <select name="user_id" class="form-select form-select-sm" style="width: auto;">
            <option value="">All Users</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i></button>
    </form>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <small class="text-muted text-uppercase d-block mb-1" style="font-size: 0.65rem;">Today</small>
                <h5 class="fw-bold text-primary mb-0">{{ $stats['today'] ?? 0 }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <small class="text-muted text-uppercase d-block mb-1" style="font-size: 0.65rem;">This Week</small>
                <h5 class="fw-bold text-success mb-0">{{ $stats['week'] ?? 0 }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <small class="text-muted text-uppercase d-block mb-1" style="font-size: 0.65rem;">Logins Today</small>
                <h5 class="fw-bold text-info mb-0">{{ $stats['logins'] ?? 0 }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <small class="text-muted text-uppercase d-block mb-1" style="font-size: 0.65rem;">Queue Actions</small>
                <h5 class="fw-bold text-warning mb-0">{{ $stats['queue_actions'] ?? 0 }}</h5>
            </div>
        </div>
    </div>
</div>

<!-- Audit Logs Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center" style="font-size: 0.8rem;">
                <thead class="bg-light">
                    <tr>
                        <th class="border-bottom-0">Time</th>
                        <th class="border-bottom-0">User</th>
                        <th class="border-bottom-0">Action</th>
                        <th class="border-bottom-0">Description</th>
                        <th class="border-bottom-0">Model</th>
                        <th class="border-bottom-0">IP Address</th>
                        <th class="border-bottom-0">Details</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td>
                            <div>{{ $log->created_at->format('M d, Y') }}</div>
                            <small class="text-muted">{{ $log->created_at->format('h:i:s A') }}</small>
                        </td>
                        <td>
                            @if($log->user)
                                <div class="fw-medium">{{ $log->user->name }}</div>
                                <small class="text-muted">{{ $log->user->role }}</small>
                            @else
                                <span class="text-muted">System</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $log->action_badge }}">
                                <i class="fas {{ $log->action_icon }} me-1"></i>
                                {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                            </span>
                        </td>
                        <td>
                            <span class="d-block text-truncate mx-auto" style="max-width: 200px;" title="{{ $log->description }}">
                                {{ $log->description }}
                            </span>
                        </td>
                        <td class="text-muted">
                            @if($log->model_type)
                                {{ class_basename($log->model_type) }} #{{ $log->model_id }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-muted">
                            <small>{{ $log->ip_address ?? '-' }}</small>
                        </td>
                        <td>
                            @if($log->old_values || $log->new_values)
                                <button class="btn btn-sm btn-outline-secondary" 
                                        onclick="showDetails({{ json_encode(['old' => $log->old_values, 'new' => $log->new_values]) }})"
                                        title="View Changes">
                                    <i class="fas fa-eye"></i>
                                </button>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-clipboard-list fa-2x mb-2 opacity-25"></i>
                            <p class="mb-0 small">No audit logs found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($logs->hasPages())
    <div class="card-footer bg-white py-2">
        {{ $logs->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function showDetails(data) {
    let html = '<div class="text-start" style="font-size: 0.85rem;">';
    
    if (data.old && Object.keys(data.old).length > 0) {
        html += '<h6 class="text-danger"><i class="fas fa-minus-circle me-1"></i>Old Values</h6>';
        html += '<pre class="bg-light p-2 rounded mb-3" style="font-size: 0.75rem; max-height: 150px; overflow: auto;">' + JSON.stringify(data.old, null, 2) + '</pre>';
    }
    
    if (data.new && Object.keys(data.new).length > 0) {
        html += '<h6 class="text-success"><i class="fas fa-plus-circle me-1"></i>New Values</h6>';
        html += '<pre class="bg-light p-2 rounded" style="font-size: 0.75rem; max-height: 150px; overflow: auto;">' + JSON.stringify(data.new, null, 2) + '</pre>';
    }
    
    html += '</div>';
    
    Swal.fire({
        title: 'Change Details',
        html: html,
        width: 500,
        showCloseButton: true,
        showConfirmButton: false
    });
}
</script>
@endpush
