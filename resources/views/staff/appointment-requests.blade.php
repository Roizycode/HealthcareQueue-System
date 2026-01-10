@extends('layouts.staff')

@section('title', 'Appointment Requests - Smart Healthcare')

@section('content')
<!-- Page Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Appointment Requests</h4>
        <p class="text-muted mb-0">Review and manage patient appointment requests</p>
    </div>
    <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2">
        <i class="fas fa-clock me-1"></i> {{ $pendingRequests->count() }} Pending
    </span>
</div>

<!-- Pending Requests -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-warning bg-opacity-10 border-0">
        <h6 class="mb-0 fw-bold text-warning">
            <i class="fas fa-hourglass-half me-2"></i>Pending Requests
        </h6>
    </div>
    <div class="card-body p-0">
        @if($pendingRequests->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 border-0">Patient</th>
                            <th class="border-0">Service</th>
                            <th class="border-0">Preferred Date</th>
                            <th class="border-0">Time</th>
                            <th class="border-0">Notes</th>
                            <th class="border-0">Submitted</th>
                            <th class="border-0 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingRequests as $request)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-medium">{{ $request->patient->full_name }}</div>
                                <div class="text-muted small">{{ $request->patient->email ?? $request->patient->phone }}</div>
                            </td>
                            <td>
                                <span class="badge" style="background: {{ $request->service->color ?? '#6c757d' }}20; color: {{ $request->service->color ?? '#6c757d' }};">
                                    {{ $request->service->name }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-medium">{{ $request->preferred_date->format('M d, Y') }}</div>
                                <div class="text-muted small">{{ $request->preferred_date->format('l') }}</div>
                            </td>
                            <td>
                                @if($request->preferred_time === 'morning')
                                    <span class="badge bg-warning bg-opacity-10 text-warning">Morning</span>
                                @else
                                    <span class="badge bg-info bg-opacity-10 text-info">Afternoon</span>
                                @endif
                            </td>
                            <td>
                                @if($request->notes)
                                    <button type="button" class="btn btn-sm btn-outline-secondary" 
                                            onclick="showPatientNotes('{{ addslashes($request->notes) }}')">
                                        <i class="fas fa-comment-dots"></i>
                                    </button>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="text-muted small">{{ $request->created_at->format('M d, g:i A') }}</div>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-success me-1" 
                                        onclick="approveRequest({{ $request->id }}, '{{ $request->patient->full_name }}', '{{ $request->service->name }}')">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                        onclick="rejectRequest({{ $request->id }}, '{{ $request->patient->full_name }}', '{{ $request->service->name }}')">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5 text-muted">
                <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                <h5>All Caught Up!</h5>
                <p class="mb-0">No pending appointment requests at the moment.</p>
            </div>
        @endif
    </div>
</div>

<!-- Recently Handled -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-light border-0">
        <h6 class="mb-0 fw-bold">
            <i class="fas fa-history me-2"></i>Recently Handled
        </h6>
    </div>
    <div class="card-body p-0">
        @if($handledRequests->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 border-0">Patient</th>
                            <th class="border-0">Service</th>
                            <th class="border-0">Date Requested</th>
                            <th class="border-0 text-center">Status</th>
                            <th class="border-0">Handled By</th>
                            <th class="border-0">Handled At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($handledRequests as $request)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-medium">{{ $request->patient->full_name }}</div>
                            </td>
                            <td>{{ $request->service->name }}</td>
                            <td>{{ $request->preferred_date->format('M d, Y') }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $request->status_badge }} bg-opacity-10 text-{{ $request->status_badge }}">
                                    {{ ucfirst($request->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="small">{{ $request->handler?->name ?? 'N/A' }}</div>
                            </td>
                            <td>
                                <div class="text-muted small">{{ $request->handled_at?->format('M d, g:i A') ?? '-' }}</div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-4 text-muted">
                <p class="mb-0">No recently handled requests.</p>
            </div>
        @endif
    </div>
</div>

<!-- Hidden Forms -->
<form id="approveForm" action="" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="staff_notes" id="approveNotes">
</form>

<form id="rejectForm" action="" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="staff_notes" id="rejectNotes">
</form>
@endsection

@push('scripts')
<script>
    function showPatientNotes(notes) {
        Swal.fire({
            title: 'Patient Notes',
            html: `<div class="bg-light rounded p-3 text-start">${notes}</div>`,
            confirmButtonColor: '#0D6EFD',
            confirmButtonText: 'Close'
        });
    }
    
    function approveRequest(id, patientName, service) {
        Swal.fire({
            title: 'Approve Request',
            html: `
                <div class="text-start mb-3">
                    <p class="mb-1"><strong>Patient:</strong> ${patientName}</p>
                    <p class="mb-0"><strong>Service:</strong> ${service}</p>
                </div>
                <div class="text-start">
                    <label class="form-label small text-muted">Add a note for the patient (optional):</label>
                    <textarea id="staffNoteInput" class="form-control" rows="2" placeholder="e.g., Please arrive 15 minutes early..."></textarea>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-check me-1"></i> Approve',
            confirmButtonColor: '#198754',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                const note = document.getElementById('staffNoteInput').value;
                const form = document.getElementById('approveForm');
                form.action = `/staff/appointment-requests/${id}/approve`;
                document.getElementById('approveNotes').value = note;
                form.submit();
            }
        });
    }
    
    function rejectRequest(id, patientName, service) {
        Swal.fire({
            title: 'Reject Request',
            html: `
                <div class="text-start mb-3">
                    <p class="mb-1"><strong>Patient:</strong> ${patientName}</p>
                    <p class="mb-0"><strong>Service:</strong> ${service}</p>
                </div>
                <div class="text-start">
                    <label class="form-label small text-muted">Reason for rejection <span class="text-danger">*</span>:</label>
                    <textarea id="rejectNoteInput" class="form-control" rows="2" placeholder="e.g., No slots available for the requested date..." required></textarea>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-times me-1"></i> Reject',
            confirmButtonColor: '#DC3545',
            cancelButtonText: 'Cancel',
            preConfirm: () => {
                const note = document.getElementById('rejectNoteInput').value;
                if (!note || note.trim() === '') {
                    Swal.showValidationMessage('Please provide a reason for rejection');
                    return false;
                }
                return note;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('rejectForm');
                form.action = `/staff/appointment-requests/${id}/reject`;
                document.getElementById('rejectNotes').value = result.value;
                form.submit();
            }
        });
    }
</script>
@endpush
