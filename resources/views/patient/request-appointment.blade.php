@extends('layouts.patient')

@section('title', 'Request Appointment - Smart Healthcare')

@section('content')
<!-- Page Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Request Appointment</h4>
        <p class="text-muted mb-0">Submit a request for your next appointment</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="patient-card p-4">
            <form action="{{ route('patient.request-appointment.submit') }}" method="POST" id="appointmentForm">
                @csrf
                
                <!-- Service Selection -->
                <div class="mb-4">
                    <label class="form-label fw-medium">Select Service <span class="text-danger">*</span></label>
                    <div class="row g-3">
                        @foreach($services as $service)
                        <div class="col-md-6">
                            <div class="form-check service-option">
                                <input class="form-check-input" type="radio" name="service_id" 
                                       id="service{{ $service->id }}" value="{{ $service->id }}"
                                       {{ old('service_id') == $service->id ? 'checked' : '' }} required>
                                <label class="form-check-label w-100" for="service{{ $service->id }}">
                                    <div class="p-3 border rounded-3 service-card" style="cursor: pointer;">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="rounded-circle p-2" style="background: {{ $service->color }}20;">
                                                <i class="fas {{ $service->icon ?? 'fa-hospital' }}" style="color: {{ $service->color }};"></i>
                                            </div>
                                            <div>
                                                <div class="fw-medium">{{ $service->name }}</div>
                                                <div class="text-muted small">{{ $service->description ?? 'General service' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @error('service_id')
                        <div class="text-danger small mt-2">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Preferred Date -->
                <div class="mb-4">
                    <label class="form-label fw-medium">Preferred Date <span class="text-danger">*</span></label>
                    <input type="date" name="preferred_date" 
                           class="form-control @error('preferred_date') is-invalid @enderror"
                           value="{{ old('preferred_date') }}"
                           min="{{ date('Y-m-d') }}"
                           required>
                    @error('preferred_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Preferred Time -->
                <div class="mb-4">
                    <label class="form-label fw-medium">Preferred Time <span class="text-danger">*</span></label>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="preferred_time" 
                                       id="timeMorning" value="morning" {{ old('preferred_time') == 'morning' ? 'checked' : '' }} required>
                                <label class="form-check-label w-100" for="timeMorning">
                                    <div class="p-3 border rounded-3 time-card" style="cursor: pointer;">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="fas fa-sun text-warning"></i>
                                            <div>
                                                <div class="fw-medium">Morning</div>
                                                <div class="text-muted small">8:00 AM - 12:00 PM</div>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="preferred_time" 
                                       id="timeAfternoon" value="afternoon" {{ old('preferred_time') == 'afternoon' ? 'checked' : '' }} required>
                                <label class="form-check-label w-100" for="timeAfternoon">
                                    <div class="p-3 border rounded-3 time-card" style="cursor: pointer;">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="fas fa-cloud-sun text-info"></i>
                                            <div>
                                                <div class="fw-medium">Afternoon</div>
                                                <div class="text-muted small">1:00 PM - 5:00 PM</div>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                    @error('preferred_time')
                        <div class="text-danger small mt-2">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Notes -->
                <div class="mb-4">
                    <label class="form-label fw-medium">Additional Notes (Optional)</label>
                    <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" 
                              rows="3" placeholder="Any specific concerns or requests...">{{ old('notes') }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <hr class="my-4">
                
                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('patient.dashboard') }}" class="btn btn-light">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
                    <button type="submit" class="btn btn-success px-4">
                        <i class="fas fa-paper-plane me-1"></i> Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="col-lg-4 mt-4 mt-lg-0">
        <div class="patient-card p-4">
            <h6 class="fw-bold mb-3">
                <i class="fas fa-info-circle text-info me-2"></i>How It Works
            </h6>
            <div class="mb-3 pb-3 border-bottom">
                <div class="d-flex gap-3">
                    <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <span class="fw-bold text-success small">1</span>
                    </div>
                    <div>
                        <div class="fw-medium small">Submit Request</div>
                        <div class="text-muted small">Fill out this form with your preferred date and time.</div>
                    </div>
                </div>
            </div>
            <div class="mb-3 pb-3 border-bottom">
                <div class="d-flex gap-3">
                    <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <span class="fw-bold text-success small">2</span>
                    </div>
                    <div>
                        <div class="fw-medium small">Wait for Approval</div>
                        <div class="text-muted small">Our staff will review your request.</div>
                    </div>
                </div>
            </div>
            <div>
                <div class="d-flex gap-3">
                    <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <span class="fw-bold text-success small">3</span>
                    </div>
                    <div>
                        <div class="fw-medium small">Get Notified</div>
                        <div class="text-muted small">You'll receive an email once your request is approved or updated.</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="patient-card p-4 mt-4">
            <div class="alert alert-warning border-0 mb-0">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Note:</strong> Appointment requests are subject to availability. You'll receive an email notification once reviewed.
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .service-card, .time-card {
        transition: all 0.2s;
    }
    
    .form-check-input:checked + .form-check-label .service-card,
    .form-check-input:checked + .form-check-label .time-card {
        border-color: #20C997 !important;
        background: rgba(32, 201, 151, 0.05);
    }
    
    .service-card:hover, .time-card:hover {
        border-color: #20C997 !important;
    }
    
    .form-check-input {
        display: none;
    }
</style>
@endpush
