@extends('layouts.app')

@section('title', 'Verify Email - Smart Healthcare')
@section('hideHeader', true)
@section('hideFooter', true)

@section('content')
<div class="login-wrapper">
    <!-- Animated Background Elements -->
    <div class="position-absolute w-100 h-100" style="overflow: hidden;">
        <div class="position-absolute rounded-circle animate-float" style="width: 400px; height: 400px; background: rgba(255,255,255,0.1); top: -100px; right: -100px;"></div>
        <div class="position-absolute rounded-circle animate-float" style="width: 250px; height: 250px; background: rgba(255,255,255,0.08); bottom: 50px; left: 5%; animation-delay: 1s;"></div>
        <div class="position-absolute rounded-circle" style="width: 80px; height: 80px; background: rgba(255,255,255,0.1); bottom: 30%; left: 20%;"></div>
    </div>

    <div class="container h-100 position-relative z-1 py-5">
        <div class="row h-100 justify-content-center align-items-center">
            <div class="col-md-6 col-lg-5">
                
                <!-- Main Card -->
                <div class="card card-healthcare border-0 shadow-lg rounded-4 animate-fadeInUp mt-5">
                    <div class="card-body p-4 p-md-5">
                        
                        <!-- Header -->
                        <div class="text-center mb-4">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                                 style="width: 70px; height: 70px; background: linear-gradient(135deg, #20C997 0%, #0dcaf0 100%);">
                                <i class="fas fa-envelope-open-text fa-xl text-white"></i>
                            </div>
                            <h3 class="fw-bold text-dark mb-1" style="font-family: 'Poppins', sans-serif;">
                                Verify Email
                            </h3>
                            <p class="text-muted small">Enter the code sent to your email</p>
                        </div>



                        <!-- Email Display -->
                        <div class="text-center mb-4">
                            <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-normal">
                                <i class="fas fa-envelope text-success me-2"></i>{{ $email }}
                            </span>
                        </div>

                        <form method="POST" action="{{ route('patient.verify.submit') }}" id="verifyForm">
                            @csrf
                            <input type="hidden" name="email" value="{{ $email }}">
                            <input type="hidden" name="code" id="codeInput">

                            <!-- Code Input -->
                            <div class="mb-4">
                                <div class="d-flex justify-content-between gap-2">
                                    <input type="text" class="form-control code-input text-center fw-bold fs-4 p-0" maxlength="1" data-index="0" inputmode="numeric" pattern="[0-9]" autocomplete="off" style="height: 50px; width: 45px;">
                                    <input type="text" class="form-control code-input text-center fw-bold fs-4 p-0" maxlength="1" data-index="1" inputmode="numeric" pattern="[0-9]" autocomplete="off" style="height: 50px; width: 45px;">
                                    <input type="text" class="form-control code-input text-center fw-bold fs-4 p-0" maxlength="1" data-index="2" inputmode="numeric" pattern="[0-9]" autocomplete="off" style="height: 50px; width: 45px;">
                                    <input type="text" class="form-control code-input text-center fw-bold fs-4 p-0" maxlength="1" data-index="3" inputmode="numeric" pattern="[0-9]" autocomplete="off" style="height: 50px; width: 45px;">
                                    <input type="text" class="form-control code-input text-center fw-bold fs-4 p-0" maxlength="1" data-index="4" inputmode="numeric" pattern="[0-9]" autocomplete="off" style="height: 50px; width: 45px;">
                                    <input type="text" class="form-control code-input text-center fw-bold fs-4 p-0" maxlength="1" data-index="5" inputmode="numeric" pattern="[0-9]" autocomplete="off" style="height: 50px; width: 45px;">
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-lg rounded-pill fw-bold shadow-sm hover-lift" 
                                        style="background: linear-gradient(135deg, #20C997 0%, #0dcaf0 100%); border: none; color: white;">
                                    Verify & Continue
                                </button>
                            </div>
                        </form>
                        
                        <!-- Resend Link -->
                        <div class="text-center mt-4 pt-3 border-top">
                            <p class="text-muted small mb-2">Didn't receive the code?</p>
                            <form method="POST" action="{{ route('patient.verify.resend') }}" class="d-inline">
                                @csrf
                                <input type="hidden" name="email" value="{{ $email }}">
                                <button type="submit" class="btn btn-link text-success p-0 fw-bold text-decoration-none small">
                                    Resend Code
                                </button>
                            </form>
                        </div>
                        </div>

                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <a href="{{ route('patient.login') }}" class="text-white small text-decoration-none opacity-75">
                         Back to Login
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
    .login-wrapper {
        min-height: 100vh;
        width: 100%;
        position: relative;
        background: linear-gradient(135deg, #20C997 0%, #0dcaf0 50%, #0D6EFD 100%);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    body::-webkit-scrollbar { display: none; }
    body { -ms-overflow-style: none; scrollbar-width: none; }
    
    .form-control:focus {
        box-shadow: none;
        background-color: #f0fdf4 !important;
        border-color: #20C997;
    }
    
    .code-input:focus {
        transform: scale(1.1);
        z-index: 10;
        box-shadow: 0 0 0 3px rgba(32, 201, 151, 0.2);
    }
    
    .code-input.filled {
        background-color: #f0fdf4;
        border-color: #20C997;
    }
    
    .hover-lift:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(32, 201, 151, 0.4) !important;
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-20px); }
    }
    
    .animate-float {
        animation: float 6s ease-in-out infinite;
    }
</style>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('.code-input');
        const codeInput = document.getElementById('codeInput');
        const form = document.getElementById('verifyForm');
        
        // Focus first input
        inputs[0].focus();
        
        function updateHiddenInput() {
            let code = '';
            inputs.forEach(input => code += input.value);
            codeInput.value = code;
        }
        
        inputs.forEach((input, index) => {
            input.addEventListener('input', function(e) {
                // Only allow numbers
                this.value = this.value.replace(/[^0-9]/g, '');
                
                if (this.value.length === 1) {
                    this.classList.add('filled');
                    // Move to next input
                    if (index < inputs.length - 1) {
                        inputs[index + 1].focus();
                    }
                } else {
                    this.classList.remove('filled');
                }
                
                updateHiddenInput();
                
                // Auto-submit if all filled
                if (codeInput.value.length === 6) {
                    setTimeout(() => form.submit(), 300);
                }
            });
            
            input.addEventListener('keydown', function(e) {
                // Handle backspace
                if (e.key === 'Backspace' && !this.value && index > 0) {
                    inputs[index - 1].focus();
                    inputs[index - 1].value = '';
                    inputs[index - 1].classList.remove('filled');
                }
            });
            
            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const text = (e.clipboardData || window.clipboardData).getData('text');
                const digits = text.replace(/[^0-9]/g, '').slice(0, 6);
                digits.split('').forEach((digit, i) => {
                    if (inputs[i]) {
                        inputs[i].value = digit;
                        inputs[i].classList.add('filled');
                    }
                });
                updateHiddenInput();
                if (digits.length === 6) {
                    inputs[5].focus();
                    setTimeout(() => form.submit(), 300);
                }
            });
        });
        
        // Error handling with SweetAlert
        @if($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Invalid Code',
                text: '{{ $errors->first() }}',
                confirmButtonColor: '#dc3545'
            });
        @endif
        
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '{{ session('success') }}',
                confirmButtonColor: '#20C997',
                timer: 3000
            });
        @endif

        @if(session('warning'))
            Swal.fire({
                icon: 'warning',
                title: 'Attention',
                text: '{{ session('warning') }}',
                confirmButtonColor: '#ffc107'
            });
        @endif
    });
</script>
@endpush
@endsection
