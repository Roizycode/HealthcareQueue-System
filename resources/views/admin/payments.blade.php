@extends('layouts.admin')

@section('title', 'Payments - HealthQueue')

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Payments</h4>
        <p class="text-muted small mb-0">View transactions and payment records</p>
    </div>
    <a href="#" class="btn btn-primary btn-sm">
        <i class="fas fa-download me-1"></i> Export
    </a>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <small class="text-muted text-uppercase d-block mb-1" style="font-size: 0.65rem;">Today's Total</small>
                <h5 class="fw-bold text-primary mb-0">₱{{ number_format($stats['total_today'], 2) }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <small class="text-muted text-uppercase d-block mb-1" style="font-size: 0.65rem;">Pending Queues</small>
                <h5 class="fw-bold text-warning mb-0">{{ $stats['pending_count'] }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <small class="text-muted text-uppercase d-block mb-1" style="font-size: 0.65rem;">Paid Transactions</small>
                <h5 class="fw-bold text-success mb-0">{{ $stats['paid_count'] }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <small class="text-muted text-uppercase d-block mb-1" style="font-size: 0.65rem;">This Month</small>
                <h5 class="fw-bold text-info mb-0">₱{{ number_format($stats['month_total'], 2) }}</h5>
            </div>
        </div>
    </div>
</div>

<!-- Payments Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0">Transactions</h6>
        <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-secondary active">All</button>
            <button class="btn btn-outline-secondary">Pending</button>
            <button class="btn btn-outline-secondary">Paid</button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center" style="font-size: 0.8rem;">
                <thead class="bg-light">
                    <tr>
                        <th class="border-bottom-0">Transaction ID</th>
                        <th class="border-bottom-0">Patient</th>
                        <th class="border-bottom-0">Service</th>
                        <th class="border-bottom-0">Amount</th>
                        <th class="border-bottom-0">Status</th>
                        <th class="border-bottom-0">Date</th>
                        <th class="border-bottom-0">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                    <tr>
                        <td class="fw-bold">TRX-{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</td>
                        <td>{{ $payment->queue->patient->full_name ?? 'Unknown' }}</td>
                        <td>{{ $payment->queue->service->name ?? '-' }}</td>
                        <td class="fw-bold">₱{{ number_format($payment->amount, 2) }}</td>
                        <td>
                            @if($payment->status === 'paid')
                                <span class="badge bg-success">Paid</span>
                            @else
                                <span class="badge bg-warning text-dark">{{ ucfirst($payment->status) }}</span>
                            @endif
                        </td>
                        <td class="text-muted">{{ $payment->created_at->format('M d, Y h:i A') }}</td>
                        <td>
                            <a href="{{ route('queue.show-ticket', $payment->queue_id) }}" target="_blank" class="btn btn-sm btn-outline-primary" title="Details">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-receipt fa-2x mb-2 opacity-25"></i>
                            <p class="mb-0 small">No transactions found</p>
                            <small class="text-muted">Payment records will appear here</small>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($payments->hasPages())
    <div class="card-footer bg-white py-2 d-flex justify-content-end">
        {{ $payments->links() }}
    </div>
    @endif
</div>
@endsection
