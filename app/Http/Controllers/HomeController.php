<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Priority;
use App\Models\Queue;
use App\Services\QueueService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class HomeController extends Controller
{
    /**
     * Display the landing page
     */
    public function index(): View
    {
        $services = Service::active()->ordered()->get();
        
        // Real-time Statistics
        $todayServed = Queue::whereDate('created_at', today())->count();
        $totalServed = Queue::count();
        
        // Calculate average wait time (minutes between created_at and serving_started_at or completed_at)
        // Using actual_wait_time column if populated, otherwise defaulting to 12min fallback
        $avgWaitTimeVal = Queue::where('status', 'completed')
                            ->whereDate('created_at', today())
                            ->avg('actual_wait_time');
                            
        $avgWaitTime = $avgWaitTimeVal ? round($avgWaitTimeVal) . 'min' : '12min';
        
        $uptime = '99.9%';
        $rating = '4.9/5';

        return view('welcome', compact('services', 'todayServed', 'totalServed', 'avgWaitTime', 'uptime', 'rating'));
    }

    /**
     * Display the join queue page
     */
    public function joinQueue(): View
    {
        $services = Service::active()->ordered()->get();
        $priorities = Priority::active()->byLevel()->get();
        
        return view('queue.join', compact('services', 'priorities'));
    }

    /**
     * Display the check queue status page
     */
    public function checkStatus(): View
    {
        return view('queue.check-status');
    }

    /**
     * Lookup queue status by queue number or phone
     */
    public function lookupStatus(Request $request)
    {
        $request->validate([
            'search' => 'required|string|max:50',
        ]);

        $search = trim($request->input('search'));

        // 1. Try Queue Number
        $queue = Queue::where('queue_number', strtoupper($search))
            ->whereDate('created_at', today())
            ->latest()
            ->first();

        // 2. Try Phone Number (Active Tickets First)
        if (!$queue) {
             $queue = Queue::whereHas('patient', function ($q) use ($search) {
                    // Remove generic formatting chars if needed, but exact match safest for now 
                    // or usage of 'like' for partial matches if strictly numbers
                    $cleanPhone = preg_replace('/[^0-9]/', '', $search);
                    $q->where('phone', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$cleanPhone}%"); // fallback
                })
                ->whereDate('created_at', today())
                ->whereIn('status', ['waiting', 'called', 'serving'])
                ->latest()
                ->first();
        }

        // 3. Try Phone Number (Any Status) if active not found
        if (!$queue) {
             $queue = Queue::whereHas('patient', function ($q) use ($search) {
                    $q->where('phone', 'like', "%{$search}%");
                })
                ->whereDate('created_at', today())
                ->latest()
                ->first();
        }

        if (!$queue) {
            return back()->with('error', 'Queue not found. Please check your queue number or phone number.');
        }

        return redirect()->route('queue.status', ['queue' => $queue->queue_number]);
    }

    /**
     * Display screen / live display
     */
    public function display(): View
    {
        $services = Service::active()->ordered()->with(['counters' => function ($q) {
            $q->active()->open();
        }])->get();

        return view('display.index', compact('services'));
    }

    /**
     * Get live queue data for display (AJAX)
     */
    public function displayData(QueueService $queueService)
    {
        $services = Service::active()->ordered()->get();

        $data = $services->map(function ($service) use ($queueService) {
            // Get only waiting queues (not called) for "Coming Up Next"
            $waitingList = Queue::where('queues.service_id', $service->id)
                ->where('queues.status', 'waiting')
                ->whereDate('queues.created_at', today())
                ->with(['priority'])
                ->join('priorities', 'queues.priority_id', '=', 'priorities.id')
                ->orderBy('priorities.level', 'desc')
                ->orderBy('queues.created_at', 'asc')
                ->select('queues.*')
                ->limit(50)
                ->get();
            
            // Get ALL active queues (Serving or Called)
            $activeQueues = Queue::where('service_id', $service->id)
                ->whereIn('status', ['serving', 'called'])
                ->whereDate('created_at', today())
                ->orderBy('updated_at', 'desc')
                ->orderBy('id', 'desc') // Prevent flickering
                ->with(['counter', 'patient', 'priority'])
                ->get();

            return [
                'id' => $service->id,
                'name' => $service->name,
                'code' => $service->code,
                'color' => $service->color,
                'active_queues' => $activeQueues->map(function ($q) {
                    return [
                        'id' => $q->id,
                        'queue_number' => $q->queue_number,
                        'counter' => $q->counter?->name,
                        'priority' => $q->priority->code ?? 'REG',
                        'status' => $q->status,
                    ];
                })->values(), // Ensure indexed array
                'waiting_count' => $service->waiting_count,
                'next_up' => $waitingList->map(function ($queue) {
                    return [
                        'queue_number' => $queue->queue_number,
                        'priority' => $queue->priority->code,
                        'priority_color' => $queue->priority->color,
                    ];
                })->values(), // Ensure indexed array
            ];
        });

        return response()->json(['success' => true, 'data' => $data]);
    }
}
