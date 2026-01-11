@extends('layouts.app')

@section('title', 'Track Queue - ' . $queue->queue_number)
@section('hideFooter', true)

@section('content')
<section class="py-4" style="min-height: 85vh; background: var(--hc-bg);">
    <div class="container py-3">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                @include('queue.partials.track-content')

                <!-- Back Link -->
                <div class="text-center mt-3 animate-fadeInUp" style="animation-delay: 0.2s;">
                    <a href="{{ route('queue.check') }}" class="text-muted text-decoration-none small">
                        Check Another Queue
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection


