@extends('layouts.patient')

@section('title', 'My Requests - Smart Healthcare')

@section('content')
<!-- Page Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">My Appointment Requests</h4>
        <p class="text-muted mb-0">Track the status of your appointment requests</p>
    </div>
    <a href="{{ route('patient.request-appointment') }}" class="btn btn-success">
        <i class="fas fa-plus me-1"></i> New Request
    </a>
</div>

<!-- Requests List -->
<div class="patient-card">
    @if($requests && count($requests) > 0)
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 border-0">Service</th>
                        <th class="border-0 text-center">Preferred Date</th>
                        <th class="border-0 text-center">Time</th>
                        <th class="border-0 text-center">Status</th>
                        <th class="border-0 text-center">Notes</th>
                        <th class="border-0 text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $request)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle p-2" style="background: {{ $request->service->color ?? '#20C997' }}20;">
                                    <i class="fas {{ $request->service->icon ?? 'fa-hospital' }}" style="color: {{ $request->service->color ?? '#20C997' }}; font-size: 0.8rem;"></i>
                                </div>
                                <span class="fw-medium">{{ $request->service->name }}</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="fw-medium">{{ $request->preferred_date->format('M d, Y') }}</div>
                            <div class="text-muted small">{{ $request->preferred_date->format('l') }}</div>
                        </td>
                        <td class="text-center">
                            @if($request->preferred_time === 'morning')
                                <span class="badge bg-warning bg-opacity-10 text-warning">
                                    <i class="fas fa-sun me-1"></i> Morning
                                </span>
                            @else
                                <span class="badge bg-info bg-opacity-10 text-info">
                                    <i class="fas fa-cloud-sun me-1"></i> Afternoon
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $request->status_badge }} bg-opacity-10 text-{{ $request->status_badge }} px-3 py-2">
                                @if($request->status === 'pending')
                                    <i class="fas fa-clock me-1"></i>
                                @elseif($request->status === 'approved')
                                    <i class="fas fa-check-circle me-1"></i>
                                @elseif($request->status === 'rejected')
                                    <i class="fas fa-times-circle me-1"></i>
                                @else
                                    <i class="fas fa-ban me-1"></i>
                                @endif
                                {{ ucfirst($request->status) }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($request->staff_notes)
                                <button type="button" class="btn btn-sm btn-outline-secondary" 
                                        onclick="showNotes('{{ $request->service->name }}', '{{ addslashes($request->staff_notes) }}')">
                                    <i class="fas fa-comment-dots me-1"></i> View
                                </button>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($request->status === 'pending')
                                <form action="{{ route('patient.cancel-request', $request->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to cancel this request?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-times"></i> Cancel
                                    </button>
                                </form>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($requests->hasPages())
        <div class="p-3 border-top">
            {{ $requests->links() }}
        </div>
        @endif
    @else
        <div class="text-center py-5">
            <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                <i class="fas fa-calendar-plus text-muted fa-2x"></i>
            </div>
            <h5 class="fw-bold mb-2">No Requests Yet</h5>
            <p class="text-muted mb-3">You haven't made any appointment requests yet.</p>
            <a href="{{ route('patient.request-appointment') }}" class="btn btn-success">
                <i class="fas fa-plus me-1"></i> Request Appointment
            </a>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    function showNotes(service, notes) {
        Swal.fire({
            title: 'Staff Notes',
            html: `
                <div class="text-start">
                    <p class="text-muted small mb-2">For: ${service}</p>
                    <div class="bg-light rounded p-3">
                        ${notes}
                    </div>
                </div>
            `,
            confirmButtonColor: '#20C997',
            confirmButtonText: 'Close'
        });
    }
</script>
@endpush
