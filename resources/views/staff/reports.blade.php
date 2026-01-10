@extends('layouts.staff')

@section('title', 'Reports & Analytics - Smart Healthcare')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Reports & Analytics</h4>
        <p class="text-muted small mb-0">Daily performance summary and audit logs</p>
    </div>
    <div class="d-flex gap-2">
        <form action="{{ route('staff.reports') }}" method="GET" class="d-flex gap-2">
            <input type="date" name="date" class="form-control" value="{{ request('date', date('Y-m-d')) }}">
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter me-1"></i> Filter</button>
        </form>
        <button onclick="window.print()" class="btn btn-outline-secondary"><i class="fas fa-print me-1"></i> Export</button>
    </div>
</div>

<!-- Summary Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-3">
                <h3 class="fw-bold text-primary mb-0">{{ $performance->sum('served') }}</h3>
                <div class="text-muted small text-uppercase">Total Served</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-3">
                <h3 class="fw-bold text-success mb-0">₱{{ number_format($revenues->sum('amount'), 2) }}</h3>
                <div class="text-muted small text-uppercase">Total Revenue</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-3">
                <h3 class="fw-bold text-info mb-0">{{ round($performance->avg('avg_wait')) }}<small class="fs-6">min</small></h3>
                <div class="text-muted small text-uppercase">Avg Wait Time</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-3">
                <h3 class="fw-bold text-warning mb-0">{{ round($performance->avg('avg_service')) }}<small class="fs-6">min</small></h3>
                <div class="text-muted small text-uppercase">Avg Service Time</div>
            </div>
        </div>
    </div>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs mb-4" id="reportTabs" role="tablist">
    <li class="nav-item"><button class="nav-link active" id="perf-tab" data-bs-toggle="tab" data-bs-target="#perf" type="button">Queue Performance</button></li>
    <li class="nav-item"><button class="nav-link" id="util-tab" data-bs-toggle="tab" data-bs-target="#util" type="button">Service Utilization</button></li>
    <li class="nav-item"><button class="nav-link" id="visit-tab" data-bs-toggle="tab" data-bs-target="#visit" type="button">Patient Visits</button></li>
    <li class="nav-item"><button class="nav-link" id="rev-tab" data-bs-toggle="tab" data-bs-target="#rev" type="button">Payment & Revenue</button></li>
    <li class="nav-item"><button class="nav-link" id="prio-tab" data-bs-toggle="tab" data-bs-target="#prio" type="button">Priority Analysis</button></li>
</ul>

<div class="tab-content" id="reportTabsContent">
    <!-- 1. Performance -->
    <div class="tab-pane show active" id="perf" role="tabpanel">
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-center">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-center">Date</th>
                            <th class="text-center">Service</th>
                            <th class="text-center">Served</th>
                            <th class="text-center">Avg Wait</th>
                            <th class="text-center">Avg Service Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($performance as $serviceName => $data)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($date)->format('M d, Y') }}</td>
                            <td>{{ $serviceName }}</td>
                            <td>{{ $data['served'] }}</td>
                            <td>{{ $data['avg_wait'] }} mins</td>
                            <td>{{ $data['avg_service'] }} mins</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-4 text-muted">No data available for this date.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 2. Utilization -->
    <div class="tab-pane" id="util" role="tabpanel">
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-center">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-center">Service</th>
                            <th class="text-center">Total Patients</th>
                            <th class="text-center">% Usage</th>
                            <th class="text-center">ProgressBar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($utilization as $serviceName => $data)
                        <tr>
                            <td>{{ $serviceName }}</td>
                            <td>{{ $data['count'] }}</td>
                            <td>{{ $data['percentage'] }}%</td>
                            <td style="width: 40%">
                                <div class="progress mx-auto" style="height: 10px; max-width: 200px;">
                                    <div class="progress-bar" role="progressbar" style="width: {{ $data['percentage'] }}%"></div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center py-4 text-muted">No data available.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 3. Visits -->
    <div class="tab-pane" id="visit" role="tabpanel">
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-center">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-center">Patient</th>
                            <th class="text-center">Visits (All Time)</th>
                            <th class="text-center">Last Visit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($patientVisits as $visit)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center justify-content-center">
                                    <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle text-primary d-flex align-items-center justify-content-center me-2" style="width:32px; height:32px">
                                        {{ substr($visit->patient->first_name ?? 'U', 0, 1) }}
                                    </div>
                                    <div class="fw-medium">{{ $visit->patient->full_name ?? 'Unknown' }}</div>
                                </div>
                            </td>
                            <td>{{ $visit->visits }}</td>
                            <td>{{ \Carbon\Carbon::parse($visit->last_visit)->format('M d, Y') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center py-4 text-muted">No patient activity found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 4. Revenue -->
    <div class="tab-pane" id="rev" role="tabpanel">
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-center">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-center">Date</th>
                            <th class="text-center">Service</th>
                            <th class="text-center">Tickets</th>
                            <th class="text-center">Amount</th>
                            <th class="text-center">Payment Method</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($revenues as $serviceName => $data)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($date)->format('M d, Y') }}</td>
                            <td>{{ $serviceName }}</td>
                            <td>{{ $data['tickets'] }}</td>
                            <td class="fw-bold text-success">₱{{ number_format($data['amount'], 2) }}</td>
                            <td>{{ $data['method'] }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-4 text-muted">No recorded payments for this date.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 5. Priority -->
    <div class="tab-pane" id="prio" role="tabpanel">
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-center">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-center">Category</th>
                            <th class="text-center">Tickets</th>
                            <th class="text-center">Avg Wait Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($priorities as $prioName => $data)
                        <tr>
                            <td>
                                <span class="badge bg-secondary text-uppercase">{{ $prioName }}</span>
                            </td>
                            <td>{{ $data['count'] }}</td>
                            <td>{{ $data['avg_wait'] }} mins</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center py-4 text-muted">No data available.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        .d-flex, .nav-tabs { display: none !important; }
        .tab-content .tab-pane { display: block !important; opacity: 1 !important; margin-bottom: 2rem; }
        .tab-pane::before { content: attr(id); font-weight: bold; font-size: 1.2rem; display: block; margin-bottom: 10px; text-transform: uppercase; }
        body { padding: 0; background: white; }
        .container, .container-fluid { max-width: 100%; margin: 0; padding: 0; }
        .card { box-shadow: none !important; border: 1px solid #ddd !important; }
    }
</style>
@endsection
