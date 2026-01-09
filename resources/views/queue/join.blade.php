@extends('layouts.app')

@section('title', 'Join Queue - HealthQueue')
@section('description', 'Register online to join the healthcare queue. Select your service and get your queue number instantly.')
@section('hideFooter', true)

@section('content')
<section class="py-4" style="min-height: 85vh; background: var(--hc-bg);">
    <div class="container py-3">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Header -->
                <div class="text-center mb-4 animate-fadeInUp">
                    <span class="badge bg-primary bg-opacity-10 text-primary px-2 py-1 rounded-pill mb-2 small fw-semibold">
                        <i class="fas fa-plus-circle me-1"></i>Virtual Queue
                    </span>
                    <h3 class="fw-bold mb-2" style="font-family: 'Poppins', sans-serif;">Join Queue Online</h3>
                    <p class="text-muted">Fill in your details to join the queue and receive your number instantly</p>
                </div>

                <!-- Registration Form Card -->
                <div class="card card-healthcare shadow animate-fadeInUp" style="animation-delay: 0.2s;">
                    <div class="card-body p-4">
                        <form action="{{ route('patient.register') }}" method="POST" id="queueForm" x-data="queueForm">
                            @csrf

                            <!-- Personal Information -->
                            <div class="mb-3">
                                <h6 class="fw-bold mb-2 d-flex align-items-center">
                                    <span class="badge bg-primary rounded-circle me-2" style="width: 22px; height: 22px; line-height: 16px; font-size: 0.75rem;">1</span>
                                    Personal Information
                                </h6>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label small">First Name <span class="text-danger">*</span></label>
                                        <input type="text" 
                                               name="first_name" 
                                               class="form-control form-control-healthcare @error('first_name') is-invalid @enderror" 
                                               value="{{ old('first_name') }}"
                                               placeholder="Enter your first name"
                                               required>
                                        @error('first_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">Last Name <span class="text-danger">*</span></label>
                                        <input type="text" 
                                               name="last_name" 
                                               class="form-control form-control-healthcare @error('last_name') is-invalid @enderror" 
                                               value="{{ old('last_name') }}"
                                               placeholder="Enter your last name"
                                               required>
                                        @error('last_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">Phone Number <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0 fw-medium text-dark">+63</span>
                                            <input type="tel" 
                                                   id="phone_display"
                                                   class="form-control form-control-healthcare border-start-0 ps-1 @error('phone') is-invalid @enderror" 
                                                   value="{{ old('phone') ? substr(old('phone'), -10) : '' }}"
                                                   placeholder="9xxxxxxxxx"
                                                   maxlength="10"
                                                   minlength="10"
                                                   pattern="9[0-9]{9}"
                                                   title="10-digit mobile number starting with 9"
                                                   oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10); document.getElementById('phone_hidden').value = this.value ? '+63' + this.value : '';"
                                                   required>
                                        </div>
                                        <input type="hidden" name="phone" id="phone_hidden" value="{{ old('phone') }}">
                                        @error('phone')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">Email Address <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted small"></i></span>
                                            <input type="email" 
                                                   name="email" 
                                                   class="form-control form-control-healthcare border-start-0 @error('email') is-invalid @enderror" 
                                                   value="{{ old('email') }}"
                                                   placeholder="your@email.com"
                                                   required>
                                        </div>
                                        @error('email')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <hr class="my-3">

                            <!-- Service Selection -->
                            <div class="mb-3">
                                <h6 class="fw-bold mb-2 d-flex align-items-center">
                                    <span class="badge bg-primary rounded-circle me-2" style="width: 22px; height: 22px; line-height: 16px; font-size: 0.75rem;">2</span>
                                    Select Service <span class="text-danger ms-1">*</span>
                                </h6>
                                <div class="row g-2">
                                    @foreach($services as $service)
                                        <div class="col-md-6">
                                            <label class="selection-card d-block h-100" 
                                                   :class="{ 'selected': selected === '{{ $service->id }}' }"
                                                   @click="selected = '{{ $service->id }}'">
                                                <input type="radio" 
                                                       class="d-none" 
                                                       name="service_id" 
                                                       value="{{ $service->id }}"
                                                       x-model="selected"
                                                       required>
                                                <div class="d-flex align-items-center">
                                                    <div class="icon-wrap me-2 flex-shrink-0" style="width: 38px; height: 38px; background: {{ $service->color ?? '#0D6EFD' }}15;">
                                                        <i class="fas {{ $service->icon ?? 'fa-hospital' }}" style="color: {{ $service->color ?? '#0D6EFD' }}; font-size: 0.9rem;"></i>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <strong class="d-block small">{{ $service->name }}</strong>
                                                        <small class="text-muted" style="font-size: 0.75rem;">
                                                            <i class="fas fa-users me-1"></i>{{ $service->waiting_count ?? 0 }} waiting
                                                            <span class="mx-1">•</span>
                                                            <i class="fas fa-clock me-1"></i>~{{ $service->average_service_time ?? 15 }}min
                                                        </small>
                                                    </div>
                                                    <i class="fas fa-check-circle text-primary ms-2" x-show="selected === '{{ $service->id }}'"></i>
                                                </div>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                @error('service_id')
                                    <div class="text-danger small mt-2">{{ $message }}</div>
                                @enderror
                            </div>

                            <hr class="my-3">

                            <!-- Priority Selection -->
                            <div class="mb-3">
                                <h6 class="fw-bold mb-2 d-flex align-items-center">
                                    <span class="badge bg-primary rounded-circle me-2" style="width: 22px; height: 22px; line-height: 16px; font-size: 0.75rem;">3</span>
                                    Priority Level <span class="text-danger ms-1">*</span>
                                </h6>
                                <div class="row g-2">
                                    @foreach($priorities as $priority)
                                        <div class="col-6 col-lg-3">
                                            <label class="selection-card d-block h-100 text-center py-3" 
                                                   :class="{ 'selected': priority === '{{ $priority->id }}' }"
                                                   @click="priority = '{{ $priority->id }}'">
                                                <input type="radio" 
                                                       class="d-none" 
                                                       name="priority_id" 
                                                       value="{{ $priority->id }}"
                                                       x-model="priority"
                                                       required>
                                                <div class="mb-1">
                                                    @if($priority->code === 'EMG')
                                                        <i class="fas fa-triangle-exclamation text-danger"></i>
                                                    @elseif($priority->code === 'SNR')
                                                        <i class="fas fa-user-clock" style="color: #9c27b0;"></i>
                                                    @elseif($priority->code === 'PWD')
                                                        <i class="fas fa-wheelchair text-info"></i>
                                                    @else
                                                        <i class="fas fa-user text-secondary"></i>
                                                    @endif
                                                </div>
                                                <strong class="d-block small">{{ $priority->name }}</strong>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                @error('priority_id')
                                    <div class="text-danger small mt-2">{{ $message }}</div>
                                @enderror
                                
                                <!-- Priority Info Alert -->
                                <div class="alert alert-healthcare alert-info-hc mt-2 py-2 small mb-0">
                                    <i class="fas fa-info-circle me-1"></i>
                                    <strong>Emergency</strong> is for life-threatening situations only. 
                                    <strong>Senior</strong> priority is for patients 60 years and above.
                                </div>
                            </div>

                            <hr class="my-3">

                            <!-- Additional Options -->
                            <div class="mb-3">
                                <h6 class="fw-bold mb-2 d-flex align-items-center">
                                    <span class="badge bg-primary rounded-circle me-2" style="width: 22px; height: 22px; line-height: 16px; font-size: 0.75rem;">4</span>
                                    Additional Information
                                </h6>
                                
                                <div class="mb-2">
                                    <label class="form-label small">Reason for Visit (Optional)</label>
                                    <textarea name="reason_for_visit" 
                                              class="form-control form-control-healthcare" 
                                              rows="2" 
                                              placeholder="Briefly describe the reason for your visit...">{{ old('reason_for_visit') }}</textarea>
                                </div>

                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="is_senior" id="is_senior" value="1" x-model="isSenior">
                                            <label class="form-check-label small" for="is_senior">
                                                <i class="fas fa-user-clock me-1 text-muted"></i>I am a Senior Citizen (60+)
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="is_pwd" id="is_pwd" value="1" x-model="isPwd">
                                            <label class="form-check-label small" for="is_pwd">
                                                <i class="fas fa-wheelchair me-1 text-muted"></i>Person with Disability (PWD)
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-healthcare btn-primary-hc py-2 rounded-pill d-flex justify-content-center align-items-center" id="submitBtn">
                                    <i class="fas fa-check-circle me-2"></i>Join Queue Now
                                </button>
                                <a href="{{ route('home') }}" class="btn btn-outline-secondary py-2 rounded-pill d-flex justify-content-center align-items-center">
                                    Back to Home
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Help Card -->
                <div class="card card-healthcare mt-3 hover-scale animate-fadeInUp" style="animation-delay: 0.4s;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="rounded-3 p-2 me-2" style="background: var(--hc-primary-bg);">
                                <i class="fas fa-headset text-primary"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 small">Need Assistance?</h6>
                                <p class="text-muted mb-0 small">
                                    Visit our reception desk or call 
                                    <a href="tel:+1234567890" class="text-primary fw-bold text-decoration-none">+1 (234) 567-8900</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    .icon-wrap {
        width: 38px;
        height: 38px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .form-check-input:checked {
        background-color: var(--hc-primary);
        border-color: var(--hc-primary);
    }
</style>
@endpush

@push('scripts')
<script>
    const prioritiesData = @json($priorities);

    // Alpine component logic
    document.addEventListener('alpine:init', () => {
        Alpine.data('queueForm', () => ({
            selected: '{{ old('service_id') }}',
            priority: '{{ old('priority_id', $priorities->where('code', 'REG')->first()?->id) }}',
            isSenior: {{ old('is_senior') ? 'true' : 'false' }},
            isPwd: {{ old('is_pwd') ? 'true' : 'false' }},

            init() {
                this.$watch('isSenior', () => this.updatePriority());
                this.$watch('isPwd', () => this.updatePriority());
            },

            updatePriority() {
                let code = 'REG';
                if (this.isSenior) code = 'SNR';
                else if (this.isPwd) code = 'PWD';
                
                // Note: If both checked, Senior takes precedence in this simple logic, 
                // or maybe we want a specific combo? Code 'SNR' usually sufficient.
                
                const p = prioritiesData.find(x => x.code === code);
                if (p) this.priority = String(p.id);
            }
        }));
    });

    document.getElementById('queueForm').addEventListener('submit', function(e) {
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    });
</script>
@endpush
