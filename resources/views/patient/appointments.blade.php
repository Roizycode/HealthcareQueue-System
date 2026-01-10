@extends('layouts.patient')

@section('title', 'My Appointments - Smart Healthcare')

@section('content')
<!-- Page Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">My Appointments</h4>
        <p class="text-muted mb-0">View your complete appointment history</p>
    </div>
</div>

@if(($upcomingAppointments && $upcomingAppointments->count() > 0) || ($appointments && $appointments->count() > 0))

    <!-- Upcoming Appointments -->
    @if($upcomingAppointments && $upcomingAppointments->count() > 0)
    <div class="mb-4">
        <h5 class="fw-bold text-dark mb-3"><i class="fas fa-calendar-check text-primary me-2"></i>Upcoming Appointments</h5>
        <div class="row g-3">
            @foreach($upcomingAppointments as $upcoming)
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4 position-relative overflow-hidden">
                        <div class="position-absolute top-0 end-0 p-3 opacity-10">
                            <i class="fas fa-calendar-alt fa-3x text-primary"></i>
                        </div>
                        <h6 class="text-uppercase text-muted small fw-bold mb-1">{{ $upcoming->service->name }}</h6>
                        <h4 class="fw-bold mb-3">{{ $upcoming->preferred_date->format('F d, Y') }}</h4>
                        
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-clock text-primary me-2"></i>
                            <span class="fw-medium">{{ $upcoming->formatted_time }}</span>
                        </div>
                        
                        @if($upcoming->staff_notes)
                        <div class="alert alert-light border small mt-3 mb-0">
                            <i class="fas fa-info-circle me-1 text-muted"></i> {{ Str::limit($upcoming->staff_notes, 50) }}
                        </div>
                        @else
                        <div class="d-flex align-items-center text-muted small mt-3">
                            <i class="fas fa-info-circle me-2"></i> Please arrive 15 mins early
                        </div>
                        @endif
                        
                        <div class="mt-3 pt-3 border-top">
                             <span class="badge bg-success bg-opacity-10 text-success">
                                <i class="fas fa-check-circle me-1"></i> Approved
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Visit History -->
    @if($appointments && $appointments->count() > 0)
    <div class="patient-card">
        <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
            <h5 class="fw-bold mb-0"><i class="fas fa-history text-secondary me-2"></i>Visit History</h5>
        </div>
        <div class="table-responsive p-3">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 border-0">Queue Number</th>
                        <th class="border-0">Service</th>
                        <th class="border-0">Date & Time</th>
                        <th class="border-0">Counter</th>
                        <th class="border-0 text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($appointments as $appointment)
                    <tr>
                        <td class="ps-4">
                            <span class="fw-bold text-success fs-5">{{ $appointment->queue_number }}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="rounded-3 p-2 me-2" style="background: {{ $appointment->service->color ?? '#20C997' }}15;">
                                    <i class="fas {{ $appointment->service->icon ?? 'fa-hospital' }}" style="color: {{ $appointment->service->color ?? '#20C997' }};"></i>
                                </div>
                                <span>{{ $appointment->service->name }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="small">
                                <div class="fw-medium">{{ $appointment->created_at->format('M d, Y') }}</div>
                                <div class="text-muted">{{ $appointment->created_at->format('g:i A') }}</div>
                            </div>
                        </td>
                        <td>
                            {{ $appointment->counter?->name ?? '-' }}
                        </td>
                        <td class="text-center">
                            @php
                                $statusConfig = [
                                    'waiting' => ['class' => 'warning', 'icon' => 'hourglass-half', 'text' => 'Waiting'],
                                    'called' => ['class' => 'success', 'icon' => 'bullhorn', 'text' => 'Called'],
                                    'serving' => ['class' => 'info', 'icon' => 'user-md', 'text' => 'Serving'],
                                    'completed' => ['class' => 'success', 'icon' => 'check-circle', 'text' => 'Completed'],
                                    'skipped' => ['class' => 'warning', 'icon' => 'forward', 'text' => 'Skipped'],
                                    'cancelled' => ['class' => 'danger', 'icon' => 'times-circle', 'text' => 'Cancelled'],
                                ];
                                $config = $statusConfig[$appointment->status] ?? ['class' => 'secondary', 'icon' => 'circle', 'text' => ucfirst($appointment->status)];
                            @endphp
                            <span class="badge bg-{{ $config['class'] }} bg-opacity-10 text-{{ $config['class'] }} px-3 py-2">
                                <i class="fas fa-{{ $config['icon'] }} me-1"></i>
                                {{ $config['text'] }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($appointments->hasPages())
        <div class="p-3 border-top">
            {{ $appointments->links() }}
        </div>
        @endif
    </div>
    @endif

@else
    <div class="patient-card">
        <div class="text-center py-5">
            <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                <i class="fas fa-calendar-times text-muted fa-2x"></i>
            </div>
            <h5 class="fw-bold mb-2">No Appointments Yet</h5>
            <p class="text-muted mb-3">You haven't made any appointments yet.</p>
            <a href="{{ route('patient.request-appointment') }}" class="btn btn-success">
                <i class="fas fa-plus me-1"></i> Request Appointment
            </a>
        </div>
    </div>
@endif
@endsection
