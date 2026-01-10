@extends('layouts.patient')

@section('title', 'Get Queue Number - Smart Healthcare')

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Get Queue Number</h4>
        <p class="text-muted mb-0">Join the current queue virtually from your dashboard.</p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="patient-card p-4">
            <form action="{{ route('patient.queue.join.submit') }}" method="POST">
                @csrf
                
                <div class="mb-4 text-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-ticket-alt text-primary fa-2x"></i>
                    </div>
                    <h5 class="fw-bold">Virtual Queue Check-in</h5>
                    <p class="text-muted small">Select a service to generate your queue ticket immediately.</p>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Select Service <span class="text-danger">*</span></label>
                    <div class="d-grid gap-2">
                        @foreach($services as $service)
                        <label class="btn btn-outline-light text-dark text-start p-3 border d-flex align-items-center hover-shadow">
                            <input type="radio" name="service_id" value="{{ $service->id }}" class="form-check-input me-3" required>
                            <div>
                                <div class="fw-bold">{{ $service->name }}</div>
                                <div class="small text-muted">{{ $service->description ?? 'General Consultation' }}</div>
                            </div>
                            <span class="badge bg-secondary ms-auto bg-opacity-10 text-dark">{{ $service->estimated_duration }} mins</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="mb-4">
                    <label for="reason" class="form-label fw-bold">Reason for Visit (Optional)</label>
                    <textarea name="reason" id="reason" rows="2" class="form-control" placeholder="Briefly describe your concern..."></textarea>
                </div>

                <div class="alert alert-info small">
                    <i class="fas fa-info-circle me-1"></i>
                    <strong>Note:</strong> Once you join, please proceed to the facility. Queues will be called based on priority.
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-check-circle me-2"></i> Get Ticket Now
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.hover-shadow:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    border-color: #20C997 !important;
    background-color: #f8f9fa;
}
.btn-outline-light:has(input:checked) {
    background-color: #e8f9f5;
    border-color: #20C997 !important;
}
</style>
@endpush
