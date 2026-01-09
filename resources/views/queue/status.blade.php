@extends('layouts.app')

@section('title', 'Queue Status - ' . $queue->queue_number)
@section('hideFooter', true)

@section('content')
<section class="py-4" style="min-height: 85vh; background: var(--hc-bg);">
    <div class="container py-3">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="card border-0 shadow rounded-4 overflow-hidden" style="max-width: 480px; margin: 0 auto;">
                    <!-- Status Header -->
                    @php
                        $statusColors = [
                            'waiting' => 'linear-gradient(135deg, #FFC107 0%, #FF9800 100%)',
                            'called' => 'linear-gradient(135deg, #0D6EFD 0%, #0dcaf0 100%)',
                            'serving' => 'linear-gradient(135deg, #20C997 0%, #198754 100%)',
                            'completed' => 'linear-gradient(135deg, #6c757d 0%, #495057 100%)',
                            'cancelled' => 'linear-gradient(135deg, #DC3545 0%, #c82333 100%)',
                            'skipped' => 'linear-gradient(135deg, #DC3545 0%, #c82333 100%)',
                        ];
                        
                        $statusMessages = [
                            'waiting' => 'You are currently in the queue',
                            'called' => 'Please proceed to the counter',
                            'serving' => 'You are being served',
                            'completed' => 'Service completed',
                            'cancelled' => 'Queue ticket cancelled',
                            'skipped' => 'You missed your turn',
                        ];

                        $bgGradient = $statusColors[$queue->status] ?? $statusColors['waiting'];
                    @endphp
                    <div class="text-white text-center py-3 position-relative" style="background: {{ $bgGradient }};">
                        <!-- Decorative Header Circles -->
                        <div class="position-absolute start-0 top-50 translate-middle rounded-circle bg-light" style="width: 20px; height: 20px;"></div>
                        <div class="position-absolute end-0 top-50 translate-middle rounded-circle bg-light" style="width: 20px; height: 20px;"></div>
                        
                        <h4 class="fw-bold mb-1 text-uppercase">{{ ucfirst($queue->status) }}</h4>
                        <p class="small mb-0 opacity-75">{{ $statusMessages[$queue->status] ?? 'Please wait' }}</p>
                    </div>

                    <div class="card-body p-0">
                        <!-- Queue Number Section -->
                        <div class="text-center py-4 bg-white">
                            <p class="text-muted text-uppercase fw-bold mb-2 small">Queue Number</p>
                            <h1 class="display-4 fw-bold mb-0 text-dark" style="font-family: 'Poppins', sans-serif;">
                                {{ $queue->queue_number }}
                            </h1>
                        </div>

                        <!-- Stats Grid -->
                        <div class="row g-0 border-top border-bottom">
                            @if($queue->status === 'completed')
                                <!-- Show Duration & Completed Time for completed services -->
                                <div class="col-6 border-end">
                                    <div class="p-3 text-center hover-bg-light transition-all">
                                        <small class="text-muted text-uppercase d-block mb-1" style="font-size: 0.7rem; font-weight: 600;">Service Duration</small>
                                        <span class="h4 fw-bold text-success mb-0">
                                            @php
                                                $duration = $queue->service_duration ?? 0;
                                            @endphp
                                            {{ $duration }} <small class="fs-6 text-muted fw-normal">min{{ $duration != 1 ? 's' : '' }}</small>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 text-center hover-bg-light transition-all">
                                        <small class="text-muted text-uppercase d-block mb-1" style="font-size: 0.7rem; font-weight: 600;">Completed At</small>
                                        <span class="h4 fw-bold text-primary mb-0">{{ $queue->completed_at?->format('g:i A') ?? '-' }}</span>
                                    </div>
                                </div>
                            @else
                                <!-- Show Position & Wait Time for active queues -->
                                <div class="col-6 border-end">
                                    <div class="p-3 text-center hover-bg-light transition-all">
                                        <small class="text-muted text-uppercase d-block mb-1" style="font-size: 0.7rem; font-weight: 600;">Current Position</small>
                                        <span class="h4 fw-bold text-primary mb-0">{{ $status['position'] ?? '-' }}</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 text-center hover-bg-light transition-all">
                                        <small class="text-muted text-uppercase d-block mb-1" style="font-size: 0.7rem; font-weight: 600;">Est. Wait Time</small>
                                        <span class="h4 fw-bold text-success mb-0">
                                            {{ $status['estimated_wait_time'] ?? '?' }} <small class="fs-6 text-muted fw-normal">min</small>
                                        </span>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Info Section -->
                        <div class="p-3 bg-light bg-opacity-25">

                            <!-- Header / Number -->
                            <div class="text-center mb-4">
                                <h6 class="text-uppercase text-muted small fw-bold mb-1">Queue Number</h6>
                                <h1 class="display-3 fw-bold text-primary mb-2">{{ $queue->queue_number }}</h1>
                                <span class="badge {{ $queue->status_badge_class }} px-3 py-2 rounded-pill">
                                    {{ $queue->status_text }}
                                </span>
                            </div>

                            <!-- Info Grid -->
                            <div class="row g-3 mb-4">
                                <div class="col-6">
                                    <div class="p-3 bg-light rounded text-center">
                                        <small class="text-muted d-block mb-1">Service</small>
                                        <div class="fw-bold text-dark">{{ $queue->service->name }}</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 bg-light rounded text-center">
                                        <small class="text-muted d-block mb-1">Wait Time</small>
                                        <div class="fw-bold text-dark">{{ $queue->wait_time }}min</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- View Ticket Link -->
                            <div class="text-center mb-4">
                                <a href="{{ route('queue.show-ticket', $queue) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-ticket-alt me-1"></i> View Printable Ticket
                                </a>
                            </div>

                            @if($queue->status === 'called' || $queue->status === 'serving')
                                <div class="alert alert-info border-0 bg-info bg-opacity-10 rounded-3 text-center mb-3 py-2 small">
                                    <i class="fas fa-bullhorn me-2"></i>
                                    <strong>Proceed to {{ $queue->counter?->name ?? 'Counter' }}</strong>
                                </div>
                            @endif

                            <!-- Collapsible Timeline -->
                            <div class="mt-3">
                                @php
                                    $timeline = [
                                        ['label' => 'Joined Queue', 'time' => $queue->checked_in_at, 'completed' => true],
                                        ['label' => 'Called', 'time' => $queue->called_at ?? $queue->serving_started_at, 'completed' => $queue->called_at !== null || in_array($queue->status, ['serving', 'completed'])],
                                        ['label' => 'Service Started', 'time' => $queue->serving_started_at ?? $queue->completed_at, 'completed' => $queue->serving_started_at !== null || $queue->status === 'completed'],
                                        ['label' => 'Completed', 'time' => $queue->completed_at, 'completed' => $queue->completed_at !== null],
                                    ];
                                @endphp
                                
                                <p class="text-muted text-uppercase fw-bold mb-3 small" style="font-size: 0.7rem;">Activity Log</p>
                                
                                <div class="position-relative ps-2">
                                    <!-- Vertical Line -->
                                    <div class="position-absolute top-0 bottom-0 border-start border-2" 
                                         style="left: 6px; border-color: #e5e7eb; z-index: 0;"></div>

                                    @foreach($timeline as $step)
                                        <div class="position-relative mb-3 ps-4">
                                            <!-- Dot -->
                                            <div class="position-absolute top-0 start-0 bg-white d-flex align-items-center justify-content-center" 
                                                 style="width: 14px; height: 14px; left: 0; z-index: 1; margin-top: 4px;">
                                                <div class="rounded-circle {{ $step['completed'] ? 'bg-primary' : 'bg-secondary bg-opacity-25' }}" 
                                                     style="width: 8px; height: 8px;"></div>
                                            </div>
                                            
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="{{ $step['completed'] ? 'text-dark fw-medium' : 'text-muted' }} small" style="font-size: 0.85rem;">{{ $step['label'] }}</span>
                                                <span class="text-muted small" style="font-size: 0.75rem;">
                                                    {{ $step['time'] ? $step['time']->format('h:i A') : '-' }}
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- My Notifications Section -->
                            <div class="mt-4 pt-3 border-top">
                                <p class="text-muted text-uppercase fw-bold mb-3 small" style="font-size: 0.7rem;">My Notifications</p>
                                
                                <div class="list-group list-group-flush small">
                                    @forelse($queue->patient->notifications()->latest()->take(10)->get() as $notification)
                                        <div class="list-group-item px-0 py-2 border-0 d-flex align-items-start bg-transparent">
                                            <div class="me-3 mt-1 text-center" style="width: 24px;">
                                                <i class="{{ $notification->data['icon'] ?? 'fas fa-info-circle' }} text-{{ $notification->data['color'] ?? 'secondary' }}"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between">
                                                    <span class="fw-medium text-dark">{{ $notification->data['title'] ?? 'Notification' }}</span>
                                                    <span class="text-muted" style="font-size: 0.7rem;">{{ $notification->created_at->format('h:i A') }}</span>
                                                </div>
                                                <div class="text-muted" style="font-size: 0.8rem;">
                                                    {{ $notification->data['message'] ?? '' }}
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-muted small text-center fst-italic py-2">No notifications yet</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <!-- Footer Actions -->
                        @if(in_array($queue->status, ['waiting', 'called']))
                        <div class="p-3 bg-white border-top">
                            <div class="row g-2">
                                <div class="col-8">
                                    <a href="{{ route('queue.status', $queue) }}" class="btn btn-primary w-100 rounded-pill fw-medium py-2 small" target="_blank">
                                        <i class="fas fa-print me-2"></i>Print Ticket
                                    </a>
                                </div>
                                <div class="col-4">
                                    <button class="btn btn-light w-100 rounded-pill text-muted border py-2" onclick="location.reload()">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Payment Section (Only if Completed) --}}
                        @if($queue->status === 'completed')
                            @if($queue->payment_status === 'pending')
                            <div class="p-3 bg-white border-top">
                                <div class="alert alert-warning border-0 bg-warning bg-opacity-10 text-center mb-3 py-2 small">
                                    <i class="fas fa-exclamation-circle me-1"></i>
                                    <strong>Payment Required</strong>
                                </div>
                                <button id="payBtn" class="btn btn-success w-100 rounded-pill fw-bold py-2 mb-3 shadow-sm" onclick="processPayment('{{ $queue->id }}')">
                                    <i class="fas fa-credit-card me-2"></i>Pay Now
                                </button>
                                <div class="text-center">
                                    <small class="text-muted d-block mb-2" style="font-size: 0.7rem;">SECURE PAYMENT VIA</small>
                                    <div class="d-flex justify-content-center gap-3 text-muted align-items-center opacity-75">
                                        <i class="fas fa-money-bill fa-lg" title="Cash"></i>
                                        <span class="fw-bold small">GCash</span>
                                        <i class="fab fa-cc-visa fa-lg" title="Card"></i>
                                    </div>
                                </div>
                            </div>
                            @elseif($queue->payment_status === 'paid')
                            <div class="p-3 bg-white border-top">
                                 <div class="alert alert-success border-0 bg-success bg-opacity-10 text-center mb-3 py-3">
                                    <i class="fas fa-check-circle fa-2x mb-2 d-block text-success"></i>
                                    <strong class="d-block">Payment Completed</strong>
                                    <small class="text-muted">Thank you for visiting!</small>
                                </div>
                                <a href="{{ route('queue.receipt', $queue) }}" target="_blank" class="btn btn-outline-dark w-100 rounded-pill fw-medium py-2">
                                    <i class="fas fa-print me-2"></i>Print Receipt
                                </a>
                            </div>
                            @endif
                        @endif
                    </div>
                </div>

                <!-- Back Link -->
                <div class="text-center mt-3 animate-fadeInUp" style="animation-delay: 0.2s;">
                    <a href="{{ route('queue.check') }}" class="text-muted text-decoration-none small">
                        <i class="fas fa-arrow-left me-1"></i>Check Another Queue
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    // Auto-refresh for active queues (Waiting/Called)
    @if(in_array($queue->status, ['waiting', 'called']))
    setTimeout(function() {
        location.reload();
    }, 15000);
    @endif

    // Payment Processing
    function processPayment(queueId) {
        Swal.fire({
            title: 'Complete Payment',
            text: "Please confirm payment for this consultation.",
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-credit-card me-2"></i> Pay Now',
            showLoaderOnConfirm: true,
            backdrop: true,
            preConfirm: () => {
                return fetch(`/queue/${queueId}/pay`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error(response.statusText)
                    return response.json()
                })
                .then(data => {
                    if (!data.success) throw new Error(data.message || 'Payment failed')
                    return data
                })
                .catch(error => {
                    Swal.showValidationMessage(`Payment Error: ${error}`)
                })
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Payment Successful!',
                    text: 'Transaction complete.',
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-print me-2"></i> Print Receipt',
                    cancelButtonText: 'Close',
                    confirmButtonColor: '#212529',
                    cancelButtonColor: '#6c757d'
                }).then((res) => {
                    if (res.isConfirmed) {
                        window.open(`/queue/${queueId}/receipt`, '_blank');
                    }
                    location.reload();
                });
            }
        });
    }
</script>
@endpush
