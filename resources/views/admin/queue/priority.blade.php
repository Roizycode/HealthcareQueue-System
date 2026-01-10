@extends('layouts.admin')

@section('title', 'Priority Queue - Smart Healthcare')

@section('content')
<!-- Header -->
<div class="mb-4">
    <h4 class="fw-bold text-dark mb-1">Priority Queue</h4>
    <p class="text-muted small mb-0">Manage queue priorities and order</p>
</div>

<!-- Priority Legend -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-2 d-flex align-items-center">
                <span class="rounded-circle me-2" style="width: 12px; height: 12px; background: #DC3545;"></span>
                <div>
                    <small class="fw-bold d-block">Emergency</small>
                    <small class="text-muted" style="font-size: 0.7rem;">Highest Priority</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-2 d-flex align-items-center">
                <span class="rounded-circle me-2" style="width: 12px; height: 12px; background: #9c27b0;"></span>
                <div>
                    <small class="fw-bold d-block">Senior Citizen</small>
                    <small class="text-muted" style="font-size: 0.7rem;">60+ years old</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-2 d-flex align-items-center">
                <span class="rounded-circle me-2" style="width: 12px; height: 12px; background: #0dcaf0;"></span>
                <div>
                    <small class="fw-bold d-block">PWD</small>
                    <small class="text-muted" style="font-size: 0.7rem;">Persons with Disability</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-2 d-flex align-items-center">
                <span class="rounded-circle me-2" style="width: 12px; height: 12px; background: #6c757d;"></span>
                <div>
                    <small class="fw-bold d-block">Regular</small>
                    <small class="text-muted" style="font-size: 0.7rem;">Standard Queue</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Priority Queues -->
<div class="row g-4">
    @foreach(['EMG' => ['Emergency', '#DC3545'], 'SNR' => ['Senior', '#9c27b0'], 'PWD' => ['PWD', '#0dcaf0'], 'REG' => ['Regular', '#6c757d']] as $code => $info)
    @php
        $filteredQueues = $queues->filter(fn($q) => $q->priority?->code === $code);
    @endphp
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-header text-white py-2" style="background: {{ $info[1] }};">
                <small class="fw-bold">{{ $info[0] }}</small>
                <span class="badge bg-white text-dark float-end">{{ $filteredQueues->count() }}</span>
            </div>
            <div class="card-body p-2" style="max-height: 300px; overflow-y: auto;">
                @forelse($filteredQueues as $queue)
                <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded mb-1" style="font-size: 0.8rem;">
                    <div>
                        <span class="fw-bold">{{ $queue->queue_number }}</span>
                        <small class="text-muted d-block">{{ $queue->patient->full_name ?? 'Unknown' }}</small>
                    </div>
                    <span class="badge bg-{{ $queue->status === 'waiting' ? 'warning text-dark' : ($queue->status === 'called' ? 'primary' : 'success') }}">{{ ucfirst($queue->status) }}</span>
                </div>
                @empty
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-inbox opacity-25"></i>
                    <p class="mb-0 small">No patients</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
