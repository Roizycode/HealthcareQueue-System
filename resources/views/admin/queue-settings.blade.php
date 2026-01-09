@extends('layouts.admin')

@section('title', 'Queue Settings - HealthQueue')

@section('content')
<!-- Header -->
<div class="mb-4">
    <h4 class="fw-bold text-dark mb-1">Queue Settings</h4>
    <p class="text-muted small mb-0">Configure queue behavior, notifications, and display options</p>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show border-0 shadow-sm py-2 small mb-4" role="alert">
    <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
    <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
</div>
@endif

<form action="{{ route('admin.settings.queue') }}" method="POST" id="queueSettingsForm">
    @csrf
    <div class="row g-4">
        <!-- Queue Behavior -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-sliders-h text-primary me-2"></i>Queue Behavior</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label small">Max Queue Size Per Day</label>
                        <input type="number" name="max_queue_size" class="form-control form-control-sm" value="{{ $settings['max_queue_size'] ?? 200 }}" min="1">
                        <small class="text-muted">Maximum number of patients that can queue in a day</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Auto-Reset Queue Daily</label>
                        <select name="auto_reset" class="form-select form-select-sm">
                            <option value="1" {{ ($settings['auto_reset'] ?? '1') === '1' ? 'selected' : '' }}>Yes - Reset at midnight</option>
                            <option value="0" {{ ($settings['auto_reset'] ?? '1') === '0' ? 'selected' : '' }}>No - Keep previous day</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Queue Number Format</label>
                        <select name="number_format" class="form-select form-select-sm">
                            <option value="service" {{ ($settings['number_format'] ?? 'service') === 'service' ? 'selected' : '' }}>Service Code + Number (GC-001)</option>
                            <option value="sequential" {{ ($settings['number_format'] ?? '') === 'sequential' ? 'selected' : '' }}>Sequential Only (001, 002)</option>
                            <option value="daily" {{ ($settings['number_format'] ?? '') === 'daily' ? 'selected' : '' }}>Date + Number (0109-001)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Wait Time Settings -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-clock text-warning me-2"></i>Wait Time</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label small">Call Timeout (seconds)</label>
                        <input type="number" name="call_timeout" class="form-control form-control-sm" value="{{ $settings['call_timeout'] ?? 120 }}" min="30">
                        <small class="text-muted">Time before auto-skip if patient doesn't respond</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Recall Attempts</label>
                        <input type="number" name="recall_attempts" class="form-control form-control-sm" value="{{ $settings['recall_attempts'] ?? 3 }}" min="1" max="5">
                        <small class="text-muted">How many times to recall before skipping</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Priority Multiplier</label>
                        <input type="number" name="priority_multiplier" class="form-control form-control-sm" value="{{ $settings['priority_multiplier'] ?? 2 }}" min="1" step="0.5">
                        <small class="text-muted">How much faster priority patients are called</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Email Notification Settings -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-envelope text-info me-2"></i>Email Notifications</h6>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="email_enabled" id="emailEnabled" value="1" {{ ($settings['email_enabled'] ?? '1') === '1' ? 'checked' : '' }}>
                        <label class="form-check-label small fw-bold" for="emailEnabled">Enable Email Notifications</label>
                    </div>
                    <hr>
                    <p class="small text-muted mb-2">Send email notifications for:</p>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="notify_queue_position" id="notifyPosition" value="1" {{ ($settings['notify_queue_position'] ?? '1') === '1' ? 'checked' : '' }}>
                        <label class="form-check-label small" for="notifyPosition">
                            <i class="fas fa-user-clock text-muted me-1"></i> When position nears (3 away)
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="notify_on_call" id="notifyCall" value="1" {{ ($settings['notify_on_call'] ?? '1') === '1' ? 'checked' : '' }}>
                        <label class="form-check-label small" for="notifyCall">
                            <i class="fas fa-phone text-muted me-1"></i> When called to counter
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="notify_on_complete" id="notifyComplete" value="1" {{ ($settings['notify_on_complete'] ?? '0') === '1' ? 'checked' : '' }}>
                        <label class="form-check-label small" for="notifyComplete">
                            <i class="fas fa-check-circle text-muted me-1"></i> When service is completed
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Display Settings -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-tv text-success me-2"></i>Display Screen</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label small">Display Theme</label>
                        <select name="display_theme" class="form-select form-select-sm">
                            <option value="dark" {{ ($settings['display_theme'] ?? 'dark') === 'dark' ? 'selected' : '' }}>Dark</option>
                            <option value="light" {{ ($settings['display_theme'] ?? '') === 'light' ? 'selected' : '' }}>Light</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Auto-scroll Speed (seconds)</label>
                        <input type="number" name="scroll_speed" class="form-control form-control-sm" value="{{ $settings['scroll_speed'] ?? 5 }}" min="3" max="30">
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="play_sound" id="playSound" value="1" {{ ($settings['play_sound'] ?? '1') === '1' ? 'checked' : '' }}>
                        <label class="form-check-label small" for="playSound">
                            <i class="fas fa-volume-up text-muted me-1"></i> Play sound on patient call
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="show_next_up" id="showNextUp" value="1" {{ ($settings['show_next_up'] ?? '1') === '1' ? 'checked' : '' }}>
                        <label class="form-check-label small" for="showNextUp">
                            <i class="fas fa-list text-muted me-1"></i> Show "Coming Up Next" section
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 d-flex justify-content-end">
        <button type="submit" class="btn btn-success">
            <i class="fas fa-save me-1"></i> Save Settings
        </button>
    </div>
</form>
@endsection
