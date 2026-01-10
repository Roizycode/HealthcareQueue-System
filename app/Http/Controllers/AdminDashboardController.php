<?php

namespace App\Http\Controllers;

use App\Models\Queue;
use App\Models\Service;
use App\Models\Priority;
use App\Models\Counter;
use App\Models\Patient;
use App\Models\User;
use App\Models\NotificationLog;
use App\Models\AuditLog;
use App\Services\QueueService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    protected QueueService $queueService;

    public function __construct(QueueService $queueService)
    {
        $this->queueService = $queueService;
    }

    /**
     * Display the admin dashboard
     */
    public function index(): View
    {
        $services = Service::active()->ordered()->get();
        $priorities = Priority::active()->byLevel()->get();
        $counters = Counter::active()->with(['service', 'assignedStaff'])->get();

        // Get overall stats
        $stats = $this->queueService->getOverallStats();

        // Get today's summary
        $todaySummary = [
            'total_patients' => Queue::today()->count(),
            'completed' => Queue::today()->completed()->count(),
            'waiting' => Queue::today()->waiting()->count(),
            'serving' => Queue::today()->serving()->count(),
            'skipped' => Queue::today()->where('status', 'skipped')->count(),
            'cancelled' => Queue::today()->where('status', 'cancelled')->count(),
            'average_wait_time' => $stats['average_wait_time'] ?? 0,
        ];

        // Get service statistics
        $serviceStats = $services->map(function ($service) {
            return [
                'id' => $service->id,
                'name' => $service->name,
                'code' => $service->code,
                'color' => $service->color,
                'waiting' => $service->waiting_count,
                'completed_today' => $service->today_completed_count,
                'average_wait_time' => $service->average_wait_time,
                'current_serving' => $service->current_serving?->queue_number,
            ];
        });

        return view('admin.dashboard', compact(
            'services',
            'priorities',
            'counters',
            'todaySummary',
            'serviceStats'
        ));
    }

    /**
     * Display service queue management page
     */
    public function serviceQueue(Service $service): View
    {
        $service->load('counters.assignedStaff');
        $priorities = Priority::active()->byLevel()->get();

        // Get waiting list
        $waitingList = $this->queueService->getWaitingList($service, 50);

        // Get current serving
        $currentServing = Queue::where('service_id', $service->id)
            ->where('status', 'serving')
            ->whereDate('created_at', today())
            ->with(['patient', 'counter'])
            ->get();

        // Get recently completed
        $recentlyCompleted = Queue::where('service_id', $service->id)
            ->where('status', 'completed')
            ->whereDate('created_at', today())
            ->with('patient')
            ->orderBy('completed_at', 'desc')
            ->limit(10)
            ->get();

        $stats = $this->queueService->getServiceStats($service);

        return view('admin.service-queue', compact(
            'service',
            'priorities',
            'waitingList',
            'currentServing',
            'recentlyCompleted',
            'stats'
        ));
    }

    /**
     * Display analytics page
     */
    public function analytics(Request $request): View
    {
        $startDate = $request->input('start_date', Carbon::today()->subDays(7)->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::today()->format('Y-m-d'));

        // Daily statistics
        $dailyStats = Queue::selectRaw('DATE(created_at) as date, COUNT(*) as total, 
            SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed,
            AVG(actual_wait_time) as avg_wait_time,
            AVG(service_duration) as avg_service_time')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Service breakdown
        $serviceBreakdown = Queue::selectRaw('service_id, COUNT(*) as total,
            SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed,
            AVG(actual_wait_time) as avg_wait_time')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->groupBy('service_id')
            ->with('service')
            ->get();

        // Priority breakdown
        $priorityBreakdown = Queue::selectRaw('priority_id, COUNT(*) as total,
            AVG(actual_wait_time) as avg_wait_time')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->groupBy('priority_id')
            ->with('priority')
            ->get();

        // Hourly distribution for today
        $hourlyDistribution = Queue::selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->whereDate('created_at', today())
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->keyBy('hour');

        // Fill missing hours
        $hourlyData = [];
        for ($i = 0; $i < 24; $i++) {
            $hourlyData[$i] = $hourlyDistribution->get($i)?->count ?? 0;
        }

        return view('admin.analytics', compact(
            'startDate',
            'endDate',
            'dailyStats',
            'serviceBreakdown',
            'priorityBreakdown',
            'hourlyData'
        ));
    }

    /**
     * Display settings page
     */
    public function settings(): View
    {
        $services = Service::ordered()->get();
        $priorities = Priority::byLevel()->get();
        $counters = Counter::with(['service', 'assignedStaff'])->get();
        $staff = User::staff()->active()->get();
        
        // Fetch Settings
        $settings = \App\Models\QueueSetting::all()->pluck('value', 'key');

        return view('admin.settings', compact('services', 'priorities', 'counters', 'staff', 'settings'));
    }

    /**
     * Display queue settings page
     */
    public function queueSettings(): View
    {
        $settings = \App\Models\QueueSetting::all()->pluck('value', 'key');
        return view('admin.queue-settings', compact('settings'));
    }

    /**
     * Save queue settings
     */
    public function saveQueueSettings(Request $request)
    {
        $settings = [
            'max_queue_size' => $request->input('max_queue_size', 200),
            'auto_reset' => $request->input('auto_reset', '1'),
            'number_format' => $request->input('number_format', 'service'),
            'call_timeout' => $request->input('call_timeout', 120),
            'recall_attempts' => $request->input('recall_attempts', 3),
            'priority_multiplier' => $request->input('priority_multiplier', 2),
            'email_enabled' => $request->has('email_enabled') ? '1' : '0',
            'notify_queue_position' => $request->has('notify_queue_position') ? '1' : '0',
            'notify_on_call' => $request->has('notify_on_call') ? '1' : '0',
            'notify_on_complete' => $request->has('notify_on_complete') ? '1' : '0',
            'display_theme' => $request->input('display_theme', 'dark'),
            'scroll_speed' => $request->input('scroll_speed', 5),
            'play_sound' => $request->has('play_sound') ? '1' : '0',
            'show_next_up' => $request->has('show_next_up') ? '1' : '0',
        ];

        foreach ($settings as $key => $value) {
            \App\Models\QueueSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return redirect()->route('admin.queue-settings')->with('success', 'Queue settings saved successfully');
    }

    /**
     * Display reports page
     */
    public function reports(Request $request): View
    {
        // Stats
        $today = now()->startOfDay();

        $stats = [
            'patients_today' => \App\Models\Queue::where('created_at', '>=', $today)->count(),
            'completed_today' => \App\Models\Queue::where('created_at', '>=', $today)->where('status', 'completed')->count(),
            'avg_wait_time' => 0,
            'avg_service_time' => 0,
        ];
        
        // Calculate averages for today
        $completedToday = \App\Models\Queue::where('created_at', '>=', $today)->where('status', 'completed')->get();
        if ($completedToday->count() > 0) {
            $stats['avg_wait_time'] = round($completedToday->avg(function($q) {
                return $q->called_at && $q->created_at ? $q->called_at->diffInMinutes($q->created_at) : 0;
            }));
            $stats['avg_service_time'] = round($completedToday->avg('service_duration'));
        }

        // Service Performance
        $serviceStats = \App\Models\Queue::select('service_id', \Illuminate\Support\Facades\DB::raw('count(*) as total'), \Illuminate\Support\Facades\DB::raw('sum(case when status="completed" then 1 else 0 end) as completed'))
            ->groupBy('service_id')
            ->with('service')
            ->get();
            
        // Staff Performance
        $staffStats = \App\Models\Queue::whereNotNull('called_by')
            ->select('called_by', \Illuminate\Support\Facades\DB::raw('count(*) as total_served'), \Illuminate\Support\Facades\DB::raw('avg(service_duration) as avg_service_time'))
            ->groupBy('called_by')
            ->with('staff')
            ->get();

        // Chart Data (Last 7 Days)
        $chartLabels = [];
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $chartLabels[] = $date->format('D');
            $chartData[] = \App\Models\Queue::whereDate('created_at', $date)->count();
        }

        return view('admin.reports', compact('stats', 'serviceStats', 'staffStats', 'chartLabels', 'chartData'));
    }

    /**
     * Generate Print-Friendly Report
     */
    public function generateReport($type)
    {
        $query = \App\Models\Queue::query();
        $period = '';

        switch ($type) {
            case 'weekly':
                $start = now()->startOfWeek();
                $end = now()->endOfWeek();
                $query->whereBetween('created_at', [$start, $end]);
                $period = $start->format('M d') . ' - ' . $end->format('M d, Y');
                break;
            case 'monthly':
                $start = now()->startOfMonth();
                $end = now()->endOfMonth();
                $query->whereBetween('created_at', [$start, $end]);
                $period = $start->format('F Y');
                break;
            case 'daily':
            default:
                $query->whereDate('created_at', today());
                $period = today()->format('F j, Y');
                break;
        }

        // Fetch Data
        $queues = $query->with(['service', 'staff', 'patient'])->get();

        // Summary
        $summary = [
            'total' => $queues->count(),
            'completed' => $queues->where('status', 'completed')->count(),
            'cancelled' => $queues->whereIn('status', ['cancelled', 'skipped'])->count(),
            'avg_wait' => 0,
        ];

        if ($summary['completed'] > 0) {
            $completed = $queues->where('status', 'completed');
            $avgWait = $completed->avg(function($q) {
                return $q->called_at && $q->created_at ? $q->called_at->diffInMinutes($q->created_at) : 0;
            });
            $summary['avg_wait'] = round($avgWait);
        }

        // Service Stats
        $serviceStats = $queues->groupBy('service_id')->map(function ($group) {
            return (object) [
                'service' => $group->first()->service,
                'total' => $group->count(),
                'completed' => $group->where('status', 'completed')->count(),
                'avg_service_time' => $group->where('status', 'completed')->avg('service_duration'),
            ];
        });

        // Staff Stats
        $staffStats = $queues->whereNotNull('called_by')->groupBy('called_by')->map(function ($group) {
            return (object) [
                'staff' => $group->first()->staff,
                'total_served' => $group->count(),
                'avg_service_time' => $group->avg('service_duration'),
            ];
        });

        // Detailed List (Limit 100)
        $details = $queues->sortByDesc('created_at')->take(100);

        return view('admin.reports_pdf', compact('type', 'period', 'summary', 'serviceStats', 'staffStats', 'details'));
    }

    /**
     * Display transactions page
     */
    public function transactions(Request $request): View
    {
        $query = \App\Models\Queue::with(['patient', 'service', 'counter', 'staff']);

        if ($request->has('date')) {
            $query->whereDate('created_at', $request->date);
        } else {
            $query->whereDate('created_at', today());
        }

        $transactions = $query->latest()->paginate(15);

        // Stats for the filtered period
        $date = $request->input('date', today());
        $baseQuery = \App\Models\Queue::whereDate('created_at', $date);
        
        $stats = [
            'total' => (clone $baseQuery)->count(),
            'completed' => (clone $baseQuery)->where('status', 'completed')->count(),
            'pending' => (clone $baseQuery)->whereIn('status', ['waiting', 'called', 'serving'])->count(),
            'cancelled' => (clone $baseQuery)->whereIn('status', ['cancelled', 'skipped'])->count(),
        ];

        return view('admin.transactions', compact('transactions', 'stats'));
    }

    /**
     * Display receipts page
     */
    public function receipts(Request $request): View
    {
        $query = \App\Models\Queue::with(['patient', 'service']);

        if ($request->has('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $receipts = $query->latest()->paginate(15);

        $stats = [
            'total' => \App\Models\Queue::count(),
            'emailed' => 0, 
            'printed' => 0,
        ];

        return view('admin.receipts', compact('receipts', 'stats'));
    }

    /**
     * Display patients list
     */
    public function patients(Request $request): View
    {
        $query = Patient::query();

        if ($search = $request->input('search')) {
            $query->search($search);
        }

        $patients = $query->orderBy('created_at', 'desc')
            ->paginate(25);

        return view('admin.patients', compact('patients'));
    }

    /**
     * Display add patient form
     */
    public function addPatient(): View
    {
        $services = Service::active()->ordered()->get();
        $priorities = Priority::active()->byLevel()->get();

        return view('admin.patients.add', compact('services', 'priorities'));
    }

    /**
     * Edit patient form
     */
    public function editPatient(Patient $patient): View
    {
        return view('admin.patients.edit', compact('patient'));
    }

    /**
     * Update patient
     */
    public function updatePatient(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100|unique:patients,email,' . $patient->id,
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'address' => 'nullable|string|max:500',
        ]);

        $patient->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('admin.patients')->with('success', 'Patient updated successfully');
    }

    /**
     * Delete patient
     */
    public function deletePatient(Patient $patient)
    {
        // Check if patient has active queues
        if ($patient->queues()->whereIn('status', ['waiting', 'called', 'serving'])->exists()) {
             return response()->json([
                'success' => false,
                'message' => 'Cannot delete patient with active queues.'
            ], 422);
        }

        $patient->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Display live queue management
     */
    public function liveQueue(): View
    {
        return view('admin.queue.live');
    }

    /**
     * Get live queue data (flat) for admin display
     */
    public function getLiveQueueData()
    {
        // Copied from StaffDashboardController for isolation
        $nowServing = \App\Models\Queue::whereIn('status', ['serving', 'called'])
            ->whereDate('created_at', today())
            ->with(['service', 'counter'])
            ->orderBy('called_at', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($q) => [
                'queue_number' => $q->queue_number,
                'service' => $q->service->name,
                'counter' => $q->counter?->name ?? 'Counter',
                'status' => $q->status,
            ]);
        
        $waiting = \App\Models\Queue::where('status', 'waiting')
            ->whereDate('created_at', today())
            ->with(['service'])
            ->orderBy('created_at', 'asc')
            ->limit(20)
            ->get()
            ->map(fn($q) => [
                'queue_number' => $q->queue_number,
                'service' => $q->service->name,
            ]);
            
        return response()->json([
            'success' => true,
            'now_serving' => $nowServing,
            'waiting' => $waiting,
            'timestamp' => now()->format('H:i:s'),
        ]);
    }

    /**
     * Display priority queue management
     */
    public function priorityQueue(): View
    {
        $priorities = Priority::active()->byLevel()->get();
        $queues = Queue::today()
            ->with(['patient', 'service', 'priority', 'counter'])
            ->whereIn('status', ['waiting', 'called', 'serving'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('admin.queue.priority', compact('priorities', 'queues'));
    }

    /**
     * Display virtual queue management
     */
    public function virtualQueue(Request $request): View
    {
        $onlineCount = Queue::today()->where('queue_type', 'online')->count();
        $walkinCount = Queue::today()->where('queue_type', 'walk_in')->count();
        $waitingCount = Queue::today()->waiting()->count();
        $completedCount = Queue::today()->completed()->count();
        
        $query = Queue::today()->with(['patient', 'service', 'priority']);
        
        // Filter by type
        if ($type = $request->input('type')) {
            if ($type === 'online') {
                $query->where('queue_type', 'online');
            } elseif ($type === 'walkin') {
                $query->where('queue_type', 'walk_in');
            }
        }
        
        $recentQueues = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.queue.virtual', compact(
            'onlineCount', 
            'walkinCount', 
            'waitingCount', 
            'completedCount', 
            'recentQueues'
        ));
    }

    /**
     * Display notification logs
     */
    /**
     * Display notification logs
     */
    public function notifications(Request $request): View
    {
        $query = \Illuminate\Notifications\DatabaseNotification::orderBy('created_at', 'desc');

        if ($type = $request->input('type')) {
             // Basic filtering if needed, though 'type' in DB is class name
        }

        $logs = $query->paginate(20);

        return view('admin.notifications', compact('logs'));
    }

    /**
     * Display payments page
     */
    public function payments(Request $request): View
    {
        $query = \App\Models\Payment::with(['queue.patient', 'queue.service'])
            ->orderBy('created_at', 'desc');

        // Note: Payments table primarily stores successful transactions.
        // Pending payments are typically Queues that are completed but not paid.
        
        $payments = $query->paginate(15);

        $stats = [
            'total_today' => \App\Models\Payment::whereDate('created_at', today())->where('status', 'paid')->sum('amount'),
            'pending_count' => \App\Models\Queue::where('status', 'completed')->where('payment_status', 'pending')->count(),
            'paid_count' => \App\Models\Payment::where('status', 'paid')->count(),
            'month_total' => \App\Models\Payment::whereMonth('created_at', now()->month)->where('status', 'paid')->sum('amount'),
        ];

        return view('admin.payments', compact('payments', 'stats'));
    }

    /**
     * Display audit logs
     */
    public function auditLogs(Request $request): View
    {
        $query = AuditLog::with('user');

        if ($action = $request->input('action')) {
            $query->where('action', $action);
        }

        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(25);

        $users = User::whereIn('role', ['admin', 'staff'])->get();

        $stats = [
            'today' => AuditLog::whereDate('created_at', today())->count(),
            'week' => AuditLog::where('created_at', '>=', now()->startOfWeek())->count(),
            'logins' => AuditLog::where('action', 'login')->whereDate('created_at', today())->count(),
            'queue_actions' => AuditLog::whereIn('action', ['queue_call', 'queue_complete', 'queue_skip'])
                ->whereDate('created_at', today())->count(),
        ];

        return view('admin.audit-logs', compact('logs', 'users', 'stats'));
    }

    /**
     * Manage services (CRUD)
     */
    public function storeService(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:10|unique:services,code',
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:7',
            'average_service_time' => 'nullable|integer|min:1|max:120',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['average_service_time'] = $validated['average_service_time'] ?? 15;

        $service = Service::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Service created successfully',
                'data' => $service,
            ]);
        }

        return redirect()->route('admin.settings')->with('success', 'Service created successfully');
    }

    public function updateService(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:10|unique:services,code,' . $service->id,
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:7',
            'average_service_time' => 'nullable|integer|min:1|max:120',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $service->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Service updated successfully',
                'data' => $service,
            ]);
        }

        return redirect()->route('admin.settings')->with('success', 'Service updated successfully');
    }

    /**
     * Toggle service active status
     */
    public function toggleService(Request $request, Service $service)
    {
        $service->is_active = $request->input('is_active', !$service->is_active);
        $service->save();

        return response()->json([
            'success' => true,
            'message' => $service->is_active ? 'Service activated' : 'Service deactivated',
            'data' => $service,
        ]);
    }

    /**
     * Manage counters
     */
    public function storeCounter(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'service_id' => 'nullable|exists:services,id',
            'assigned_staff_id' => 'nullable|exists:users,id',
            'status' => 'nullable|in:open,closed,break',
        ]);

        // Auto-generate code from name
        $validated['code'] = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $validated['name']), 0, 5));
        $validated['status'] = $validated['status'] ?? 'closed';
        $validated['is_active'] = true;

        $counter = Counter::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Counter created successfully',
                'data' => $counter,
            ]);
        }

        return redirect()->route('admin.settings')->with('success', 'Counter created successfully');
    }

    public function updateCounter(Request $request, Counter $counter)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'service_id' => 'nullable|exists:services,id',
            'assigned_staff_id' => 'nullable|exists:users,id',
        ]);

        $counter->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Counter updated successfully',
                'data' => $counter,
            ]);
        }

        return redirect()->route('admin.settings')->with('success', 'Counter updated successfully');
    }

    /**
     * Toggle counter status
     */
    public function toggleCounterStatus(Counter $counter, string $status)
    {
        if (!in_array($status, ['open', 'closed', 'break'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status',
            ], 422);
        }

        $counter->status = $status;
        $counter->save();

        return response()->json([
            'success' => true,
            'message' => "Counter is now {$status}",
            'data' => $counter,
        ]);
    }
}
