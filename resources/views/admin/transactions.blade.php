@extends('layouts.admin')

@section('title', 'Transactions - Smart Healthcare')

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Transactions</h4>
        <p class="text-muted small mb-0">View all queue transactions and activities</p>
    </div>
    <div class="d-flex gap-2">
        <input type="date" class="form-control form-control-sm" id="dateFilter" value="{{ date('Y-m-d') }}">
        <button class="btn btn-primary btn-sm" onclick="filterTransactions()">
            <i class="fas fa-filter"></i>
        </button>
    </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-primary text-white">
            <div class="card-body text-center py-3">
                <small class="text-white-50 text-uppercase d-block mb-1" style="font-size: 0.65rem;">Total Today</small>
                <h4 class="fw-bold mb-0">{{ $stats['total'] ?? 0 }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-success text-white">
            <div class="card-body text-center py-3">
                <small class="text-white-50 text-uppercase d-block mb-1" style="font-size: 0.65rem;">Completed</small>
                <h4 class="fw-bold mb-0">{{ $stats['completed'] ?? 0 }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-warning text-dark">
            <div class="card-body text-center py-3">
                <small class="text-dark-50 text-uppercase d-block mb-1" style="font-size: 0.65rem;">Pending</small>
                <h4 class="fw-bold mb-0">{{ $stats['pending'] ?? 0 }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-danger text-white">
            <div class="card-body text-center py-3">
                <small class="text-white-50 text-uppercase d-block mb-1" style="font-size: 0.65rem;">Cancelled/Skipped</small>
                <h4 class="fw-bold mb-0">{{ $stats['cancelled'] ?? 0 }}</h4>
            </div>
        </div>
    </div>
</div>

<!-- Transactions Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3">
        <h6 class="fw-bold mb-0"><i class="fas fa-exchange-alt text-primary me-2"></i>Transaction History</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center" style="font-size: 0.8rem;">
                <thead class="bg-light">
                    <tr>
                        <th class="border-bottom-0">Queue #</th>
                        <th class="border-bottom-0">Patient</th>
                        <th class="border-bottom-0">Service</th>
                        <th class="border-bottom-0">Counter</th>
                        <th class="border-bottom-0">Staff</th>
                        <th class="border-bottom-0">Status</th>
                        <th class="border-bottom-0">Time</th>
                        <th class="border-bottom-0">Duration</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions ?? [] as $queue)
                    <tr>
                        <td class="fw-bold">{{ $queue->queue_number }}</td>
                        <td>{{ $queue->patient->full_name ?? 'Guest' }}</td>
                        <td>
                            <span class="badge" style="background: {{ $queue->service->color ?? '#6c757d' }}">{{ $queue->service->name ?? '-' }}</span>
                        </td>
                        <td>{{ $queue->counter->name ?? '-' }}</td>
                        <td>{{ $queue->staff->name ?? '-' }}</td>
                        <td>
                            @php
                                $statusColors = [
                                    'waiting' => 'bg-warning text-dark',
                                    'called' => 'bg-info',
                                    'serving' => 'bg-primary',
                                    'completed' => 'bg-success',
                                    'skipped' => 'bg-secondary',
                                    'cancelled' => 'bg-danger'
                                ];
                            @endphp
                            <span class="badge {{ $statusColors[$queue->status] ?? 'bg-light text-dark' }}">{{ ucfirst($queue->status) }}</span>
                        </td>
                        <td class="text-muted">{{ $queue->created_at->format('h:i A') }}</td>
                        <td>
                            @if($queue->service_duration)
                                {{ $queue->service_duration }} min
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fas fa-exchange-alt fa-2x mb-2 opacity-25"></i>
                            <p class="mb-0 small">No transactions found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if(isset($transactions) && $transactions->hasPages())
    <div class="card-footer bg-white py-2 d-flex justify-content-end">
        {{ $transactions->links() }}
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function filterTransactions() {
    const date = document.getElementById('dateFilter').value;
    window.location.href = '{{ route("admin.transactions") }}?date=' + date;
}
</script>
@endpush
