@extends('layouts.admin')

@section('title', 'Receipts - HealthQueue')

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Receipts</h4>
        <p class="text-muted small mb-0">View and manage queue receipts</p>
    </div>
    <form action="{{ route('admin.receipts') }}" method="GET" class="d-flex gap-2">
        <input type="date" name="date" class="form-control form-control-sm" value="{{ request('date', date('Y-m-d')) }}">
        <button type="submit" class="btn btn-primary btn-sm">
            <i class="fas fa-filter"></i>
        </button>
    </form>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <small class="text-muted text-uppercase d-block mb-1" style="font-size: 0.65rem;">Receipts Generated</small>
                <h4 class="fw-bold text-primary mb-0">{{ $stats['total'] ?? 0 }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <small class="text-muted text-uppercase d-block mb-1" style="font-size: 0.65rem;">Email Sent</small>
                <h4 class="fw-bold text-success mb-0">{{ $stats['emailed'] ?? 0 }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <small class="text-muted text-uppercase d-block mb-1" style="font-size: 0.65rem;">Printed</small>
                <h4 class="fw-bold text-info mb-0">{{ $stats['printed'] ?? 0 }}</h4>
            </div>
        </div>
    </div>
</div>

<!-- Receipts Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3">
        <h6 class="fw-bold mb-0"><i class="fas fa-receipt text-success me-2"></i>Receipt History</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center" style="font-size: 0.8rem;">
                <thead class="bg-light">
                    <tr>
                        <th class="border-bottom-0">Receipt #</th>
                        <th class="border-bottom-0">Queue #</th>
                        <th class="border-bottom-0">Patient</th>
                        <th class="border-bottom-0">Service</th>
                        <th class="border-bottom-0">Date/Time</th>
                        <th class="border-bottom-0">Status</th>
                        <th class="border-bottom-0">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($receipts ?? [] as $queue)
                    <tr>
                        <td class="fw-bold">RCP-{{ str_pad($queue->id, 6, '0', STR_PAD_LEFT) }}</td>
                        <td>{{ $queue->queue_number }}</td>
                        <td>{{ $queue->patient->full_name ?? 'Guest' }}</td>
                        <td>
                            <span class="badge" style="background: {{ $queue->service->color ?? '#6c757d' }}">{{ $queue->service->name ?? '-' }}</span>
                        </td>
                        <td class="text-muted">{{ $queue->created_at->format('M d, Y h:i A') }}</td>
                        <td>
                            @if($queue->status === 'completed')
                                <span class="badge bg-success">Completed</span>
                            @else
                                <span class="badge bg-warning text-dark">Pending</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" onclick="viewReceipt({{ $queue->id }})" title="View">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <a href="{{ route('queue.show-ticket', $queue) }}" target="_blank" class="btn btn-outline-success" title="Print">
                                    <i class="fas fa-print"></i>
                                </a>
                                <button class="btn btn-outline-info" onclick="emailReceipt({{ $queue->id }})" title="Email">
                                    <i class="fas fa-envelope"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-receipt fa-2x mb-2 opacity-25"></i>
                            <p class="mb-0 small">No receipts found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if(isset($receipts) && $receipts->hasPages())
    <div class="card-footer bg-white py-2 d-flex justify-content-end">
        {{ $receipts->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function viewReceipt(id) {
    Swal.fire({
        title: 'Receipt Preview',
        html: '<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Loading receipt...</p></div>',
        showConfirmButton: false,
        didOpen: () => {
            // Simulate loading receipt
            setTimeout(() => {
                Swal.update({
                    html: `
                        <div class="text-start p-3 bg-light rounded border" style="font-family: 'Inter', monospace; font-size: 0.9rem;">
                            <div class="text-center mb-3">
                                <i class="fas fa-hospital-user text-primary fa-2x mb-2"></i>
                                <h6 class="fw-bold mb-0">HealthCare Queue System</h6>
                                <small class="text-muted">Official Receipt</small>
                            </div>
                            <div class="border-top border-bottom border-dark py-2 my-2 border-opacity-25">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Receipt #:</span>
                                    <span class="fw-bold">RCP-${String(id).padStart(6, '0')}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Date:</span>
                                    <span class="fw-bold">${new Date().toLocaleDateString()}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Status:</span>
                                    <span class="badge bg-success">Completed</span>
                                </div>
                            </div>
                            <div class="text-center mt-4">
                                <p class="mb-0 fw-bold">Thank you for visiting!</p>
                                <small class="text-muted">Please come again.</small>
                            </div>
                        </div>
                    `,
                    showConfirmButton: true,
                    confirmButtonColor: '#000',
                    confirmButtonText: 'Close'
                });
            }, 500);
        }
    });
}

function emailReceipt(id) {
    Swal.fire({
        title: 'Email Receipt',
        input: 'email',
        inputLabel: 'Enter email address',
        inputPlaceholder: 'patient@email.com',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-paper-plane me-1"></i> Send'
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            Swal.fire({
                icon: 'success',
                title: 'Email Sent!',
                text: 'Receipt sent to ' + result.value,
                timer: 2000,
                showConfirmButton: false
            });
        }
    });
}
</script>
@endpush
