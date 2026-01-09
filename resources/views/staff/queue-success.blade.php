@extends('layouts.staff')

@section('title', 'Ticket Created - HealthQueue')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 text-center">
        <div class="mb-4">
            <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                <i class="fas fa-check text-success fa-3x"></i>
            </div>
        </div>
        <h2 class="fw-bold text-dark">Patient Registered!</h2>
        <p class="text-muted">The patient has been added to the queue.</p>

        <div class="card border-0 shadow-lg my-4 position-relative overflow-hidden tickets-card">
            <div class="card-body p-5">
                <p class="text-uppercase small letter-spacing-2 text-muted mb-2">Queue Ticket</p>
                <h1 class="display-1 fw-bold text-primary mb-2">{{ $queue->queue_number }}</h1>
                <h4 class="fw-medium text-dark mb-4">{{ $queue->service->name }}</h4>

                <div class="border-top pt-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Patient Name</span>
                        <span class="fw-bold">{{ $queue->patient->full_name }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Priority</span>
                        <span class="badge bg-light text-dark border">{{ $queue->priority->name }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Date</span>
                        <span class="fw-bold">{{ $queue->created_at->format('M d, Y h:i A') }}</span>
                    </div>
                </div>
            </div>
            <div class="position-absolute top-0 start-0 w-100 h-2 bg-primary"></div>
        </div>

        <div class="d-grid gap-2 d-sm-flex justify-content-center no-print">
            <button onclick="window.print()" class="btn btn-lg btn-outline-secondary px-4">
                <i class="fas fa-print me-2"></i> Print Ticket
            </button>
            <a href="{{ route('staff.patients.add') }}" class="btn btn-lg btn-primary px-4">
                <i class="fas fa-plus me-2"></i> Add Another
            </a>
        </div>
        <div class="mt-3 no-print">
            <a href="{{ route('staff.dashboard') }}" class="text-muted text-decoration-none small">
                Back to Dashboard
            </a>
        </div>
    </div>
</div>

<style>
    @media print {
        body * { visibility: hidden; }
        .tickets-card, .tickets-card * { visibility: visible; }
        .tickets-card { position: absolute; left: 0; top: 0; width: 100%; border: 2px solid #000 !important; box-shadow: none !important; }
        .no-print { display: none !important; }
        .bg-success { display: none !important; }
        nav, header, footer { display: none !important; }
    }
</style>
@endsection
