<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Queue;
use App\Models\Service;
use App\Models\Priority;
use App\Services\QueueService;
use App\Models\AppointmentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PatientDashboardController extends Controller
{
    protected QueueService $queueService;

    public function __construct(QueueService $queueService)
    {
        $this->queueService = $queueService;
    }

    /**
     * Display the patient dashboard
     */
    public function index(): View
    {
        $user = Auth::user();
        $patient = $user->patient;
        
        // Get patient's queue history
        $queueHistory = [];
        $activeQueue = null;
        $stats = [
            'total_visits' => 0,
            'completed' => 0,
            'cancelled' => 0,
            'pending' => 0,
        ];
        
        if ($patient) {
            // Active queue (today, waiting/called/serving)
            $activeQueue = Queue::where('patient_id', $patient->id)
                ->whereIn('status', ['waiting', 'called', 'serving'])
                ->whereDate('created_at', today())
                ->with(['service', 'priority', 'counter'])
                ->first();
            
            // Queue history
            $queueHistory = Queue::where('patient_id', $patient->id)
                ->with(['service', 'priority'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
            
            // Stats
            $stats['total_visits'] = Queue::where('patient_id', $patient->id)->count();
            $stats['completed'] = Queue::where('patient_id', $patient->id)->where('status', 'completed')->count();
            $stats['cancelled'] = Queue::where('patient_id', $patient->id)->where('status', 'cancelled')->count();
            $stats['pending'] = Queue::where('patient_id', $patient->id)->whereIn('status', ['waiting', 'called', 'serving'])->count();
        }
        
        $services = Service::active()->ordered()->get();
        
        return view('patient.dashboard', compact('user', 'patient', 'activeQueue', 'queueHistory', 'stats', 'services'));
    }

    /**
     * Show patient's appointments/history
     */
    public function appointments(): View
    {
        $user = Auth::user();
        $patient = $user->patient;
        
        $appointments = [];
        $upcomingAppointments = [];

        if ($patient) {
            // Past/Current Queue Tickets
            $appointments = Queue::where('patient_id', $patient->id)
                ->with(['service', 'priority', 'counter'])
                ->orderBy('created_at', 'desc')
                ->paginate(10); // Reduced pagination for space

            // Upcoming Approved Appointments
            $upcomingAppointments = AppointmentRequest::where('patient_id', $patient->id)
                ->where('status', 'approved')
                ->whereDate('preferred_date', '>=', today())
                ->with('service')
                ->orderBy('preferred_date', 'asc')
                ->get();
        }
        
        return view('patient.appointments', compact('user', 'patient', 'appointments', 'upcomingAppointments'));
    }

    /**
     * Show patient profile
     */
    public function profile(): View
    {
        $user = Auth::user();
        $patient = $user->patient;
        
        return view('patient.profile', compact('user', 'patient'));
    }

    /**
     * Update patient profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => ['nullable', 'string', 'max:15'],
        ]);
        
        $user->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? $user->phone,
        ]);
        
        // Update patient record if exists
        if ($user->patient) {
            $names = explode(' ', $validated['name'], 2);
            $user->patient->update([
                'first_name' => $names[0],
                'last_name' => $names[1] ?? '',
                'phone' => $validated['phone'] ?? $user->patient->phone,
            ]);
        }
        
        return back()->with('success', 'Profile updated successfully!');
    }

    /**
     * Get active queue status via AJAX
     */
    public function getQueueStatus()
    {
        $user = Auth::user();
        $patient = $user->patient;
        
        if (!$patient) {
            return response()->json(['success' => false, 'message' => 'No patient profile found']);
        }
        
        $activeQueue = Queue::where('patient_id', $patient->id)
            ->whereIn('status', ['waiting', 'called', 'serving'])
            ->whereDate('created_at', today())
            ->with(['service', 'priority', 'counter'])
            ->first();
        
        if (!$activeQueue) {
            return response()->json(['success' => true, 'data' => null]);
        }
        
        // Calculate position in queue
        $position = Queue::where('service_id', $activeQueue->service_id)
            ->where('status', 'waiting')
            ->whereDate('created_at', today())
            ->where('created_at', '<', $activeQueue->created_at)
            ->count() + 1;
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $activeQueue->id,
                'queue_number' => $activeQueue->queue_number,
                'status' => $activeQueue->status,
                'service' => $activeQueue->service->name,
                'counter' => $activeQueue->counter?->name,
                'position' => $activeQueue->status === 'waiting' ? $position : 0,
                'estimated_wait' => $activeQueue->status === 'waiting' ? ($position * 10) : 0,
            ]
        ]);
    }

    /**
     * Show queue check page (embedded in patient dashboard)
     */
    public function checkQueue(): View
    {
        $user = Auth::user();
        $patient = $user->patient;
        
        $activeQueue = null;
        $position = 0;
        
        if ($patient) {
            $activeQueue = Queue::where('patient_id', $patient->id)
                ->whereIn('status', ['waiting', 'called', 'serving'])
                ->whereDate('created_at', today())
                ->with(['service', 'priority', 'counter'])
                ->first();
            
            if ($activeQueue && $activeQueue->status === 'waiting') {
                $position = Queue::where('service_id', $activeQueue->service_id)
                    ->where('status', 'waiting')
                    ->whereDate('created_at', today())
                    ->where('created_at', '<', $activeQueue->created_at)
                    ->count() + 1;
            }
        }
        
        return view('patient.check-queue', compact('user', 'patient', 'activeQueue', 'position'));
    }

    /**
     * Show live display page (embedded in patient dashboard)
     */
    public function liveDisplay(): View
    {
        $user = Auth::user();
        $patient = $user->patient;
        
        // Get now serving queues
        $nowServing = Queue::whereIn('status', ['serving', 'called'])
            ->whereDate('created_at', today())
            ->with(['service', 'counter', 'patient'])
            ->orderBy('called_at', 'desc')
            ->limit(6)
            ->get();
        
        // Get waiting queues
        $waitingQueues = Queue::where('status', 'waiting')
            ->whereDate('created_at', today())
            ->with(['service', 'priority'])
            ->orderBy('created_at', 'asc')
            ->limit(10)
            ->get();
        
        return view('patient.live-display', compact('user', 'patient', 'nowServing', 'waitingQueues'));
    }

    /**
     * Get real-time queue data for AJAX polling
     */
    public function getQueueData()
    {
        $user = Auth::user();
        $patient = $user->patient;
        
        // Get now serving
        $nowServing = Queue::whereIn('status', ['serving', 'called'])
            ->whereDate('created_at', today())
            ->with(['service', 'counter'])
            ->orderBy('called_at', 'desc')
            ->limit(6)
            ->get()
            ->map(fn($q) => [
                'queue_number' => $q->queue_number,
                'service' => $q->service->name,
                'counter' => $q->counter?->name ?? 'Counter',
                'status' => $q->status,
            ]);
        
        // Get waiting
        $waiting = Queue::where('status', 'waiting')
            ->whereDate('created_at', today())
            ->with(['service'])
            ->orderBy('created_at', 'asc')
            ->limit(10)
            ->get()
            ->map(fn($q) => [
                'queue_number' => $q->queue_number,
                'service' => $q->service->name,
            ]);
        
        // Get patient's own queue status
        $myQueue = null;
        if ($patient) {
            $activeQueue = Queue::where('patient_id', $patient->id)
                ->whereIn('status', ['waiting', 'called', 'serving'])
                ->whereDate('created_at', today())
                ->with(['service', 'counter'])
                ->first();
            
            if ($activeQueue) {
                $position = 0;
                if ($activeQueue->status === 'waiting') {
                    $position = Queue::where('service_id', $activeQueue->service_id)
                        ->where('status', 'waiting')
                        ->whereDate('created_at', today())
                        ->where('created_at', '<', $activeQueue->created_at)
                        ->count() + 1;
                }
                
                $myQueue = [
                    'queue_number' => $activeQueue->queue_number,
                    'status' => $activeQueue->status,
                    'service' => $activeQueue->service->name,
                    'counter' => $activeQueue->counter?->name,
                    'position' => $position,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'now_serving' => $nowServing,
            'waiting' => $waiting,
            'my_queue' => $myQueue,
            'timestamp' => now()->format('H:i:s'),
        ]);
    }

    /**
     * Get real-time dashboard stats for patient
     */
    public function getDashboardStats()
    {
        $user = Auth::user();
        $patient = $user->patient;

        if (!$patient) {
            return response()->json([
                'success' => true,
                'stats' => [
                    'total_visits' => 0,
                    'completed' => 0,
                    'cancelled' => 0,
                    'pending' => 0,
                ],
                'active_queue' => null,
                'history' => []
            ]);
        }

        // Fetch Requests (Pending)
        $pendingRequestsCount = AppointmentRequest::where('patient_id', $patient->id)
            ->where('status', 'pending')
            ->count();
            
        // Fetch Queues
        $queues = Queue::where('patient_id', $patient->id);

        // Stats
        $stats = [
            'total_visits' => (clone $queues)->count(),
            'completed' => (clone $queues)->where('status', 'completed')->count(),
            'cancelled' => (clone $queues)->where('status', 'cancelled')->count() + AppointmentRequest::where('patient_id', $patient->id)->where('status', 'rejected')->count(),
            'pending' => (clone $queues)->whereIn('status', ['waiting', 'called', 'serving'])->count() + $pendingRequestsCount,
        ];

        // Active Queue (Today)
        $activeQueue = Queue::where('patient_id', $patient->id)
            ->whereIn('status', ['waiting', 'called', 'serving'])
            ->whereDate('created_at', today())
            ->with(['service', 'counter'])
            ->first();

        // Queues for History
        $queueHistory = Queue::where('patient_id', $patient->id)
            ->with(['service'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(fn($q) => [
                'type' => 'queue',
                'queue_number' => $q->queue_number,
                'service' => $q->service->name,
                'created_at' => $q->created_at, // Keep as object for sorting
                'date_formatted' => $q->created_at->format('M d, Y'),
                'status' => ucfirst($q->status),
                'status_raw' => $q->status
            ]);

        // Requests for History
        $requestHistory = AppointmentRequest::where('patient_id', $patient->id)
            ->with(['service'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(fn($r) => [
                'type' => 'request',
                'queue_number' => 'REQ-' . $r->id, // Pseudo number
                'service' => $r->service->name,
                'created_at' => $r->created_at,
                'date_formatted' => $r->created_at->format('M d, Y'),
                'status' => ucfirst($r->status),
                'status_raw' => $r->status === 'pending' ? 'pending-request' : $r->status
            ]);

        // Merge and Sort
        $history = $queueHistory->merge($requestHistory)
            ->sortByDesc('created_at')
            ->take(5)
            ->values()
            ->map(fn($item) => [
                'queue_number' => $item['queue_number'],
                'service' => $item['service'],
                'created_at' => $item['date_formatted'],
                'status' => $item['status'],
                'status_raw' => $item['status_raw']
            ]);

        // Transform Active Queue for JSON
        $activeQueueData = null;
        if ($activeQueue) {
            $activeQueueData = [
                'queue_number' => $activeQueue->queue_number,
                'service' => $activeQueue->service->name,
                'status' => $activeQueue->status,
                'counter' => $activeQueue->counter?->name ?? 'Counter',
            ];
        }

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'active_queue' => $activeQueueData,
            'history' => $history
        ]);
    }

    /**
     * Show appointment request form
     */
    public function requestAppointment(): View
    {
        $user = Auth::user();
        $patient = $user->patient;
        $services = Service::active()->ordered()->get();
        
        return view('patient.request-appointment', compact('user', 'patient', 'services'));
    }

    /**
     * Submit appointment request
     */
    public function submitAppointmentRequest(Request $request)
    {
        $user = Auth::user();
        $patient = $user->patient;
        
        if (!$patient) {
            return back()->with('error', 'Patient profile not found.');
        }
        
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'preferred_date' => 'required|date|after_or_equal:today',
            'preferred_time' => 'required|in:morning,afternoon',
            'notes' => 'nullable|string|max:500',
        ]);
        
        $appointmentRequest = \App\Models\AppointmentRequest::create([
            'patient_id' => $patient->id,
            'service_id' => $validated['service_id'],
            'preferred_date' => $validated['preferred_date'],
            'preferred_time' => $validated['preferred_time'],
            'notes' => $validated['notes'],
            'status' => 'pending',
        ]);
        
        // Send email notification
        try {
            $user->notify(new \App\Notifications\AppointmentRequestStatus($appointmentRequest, 'submitted'));
        } catch (\Exception $e) {
            \Log::error('Failed to send appointment request email: ' . $e->getMessage());
        }
        
        return redirect()->route('patient.my-requests')->with('success', 'Appointment request submitted successfully! You will receive an email notification once it is reviewed.');
    }

    /**
     * Show patient's appointment requests
     */
    public function myRequests(): View
    {
        $user = Auth::user();
        $patient = $user->patient;
        
        $requests = [];
        if ($patient) {
            $requests = \App\Models\AppointmentRequest::where('patient_id', $patient->id)
                ->with(['service', 'handler'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        }
        
        return view('patient.my-requests', compact('user', 'patient', 'requests'));
    }

    /**
     * Cancel a pending request
     */
    public function cancelRequest($id)
    {
        $user = Auth::user();
        $patient = $user->patient;
        
        if (!$patient) {
            return back()->with('error', 'Patient profile not found.');
        }
        
        $request = \App\Models\AppointmentRequest::where('id', $id)
            ->where('patient_id', $patient->id)
            ->where('status', 'pending')
            ->firstOrFail();
        
        $request->update([
            'status' => 'cancelled',
        ]);
        
        return back()->with('success', 'Appointment request cancelled.');
    }

    /**
     * Show join active queue form
     */
    public function joinQueueView()
    {
        $services = Service::active()->get();
        return view('patient.join-queue', compact('services'));
    }

    /**
     * Submit join active queue
     */
    public function joinQueueSubmit(Request $request)
    {
        $user = Auth::user();
        $patient = $user->patient;

        if (!$patient) {
            return back()->with('error', 'Patient profile not found. Please complete your profile.');
        }

        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'reason' => 'nullable|string|max:255',
        ]);

        // Check for existing active queue
        $existingQueue = Queue::where('patient_id', $patient->id)
            ->whereIn('status', ['waiting', 'called', 'serving'])
            ->whereDate('created_at', today())
            ->first();

        if ($existingQueue) {
            return redirect()->route('patient.queue-check')->with('info', 'You are already in the queue.');
        }

        $service = Service::find($validated['service_id']);
        
        // Determine Priority based on profile
        $priorityCode = 'REG';
        if ($patient->is_senior) $priorityCode = 'SNR';
        else if ($patient->is_pwd) $priorityCode = 'PWD';
        else if ($patient->pregnant) $priorityCode = 'PREG'; // If exists

        $priority = Priority::where('code', $priorityCode)->first();
        if (!$priority) $priority = Priority::first(); // Fallback

        try {
            $queue = $this->queueService->joinQueue(
                $patient,
                $service,
                $priority,
                'virtual',
                $validated['reason'] ?? 'Dashboard Join'
            );

            $patient->notify(new \App\Notifications\QueueStatusUpdated($queue, 'joined'));

            return redirect()->route('patient.queue-check')->with('success', "You have joined the queue! Your Ticket: {$queue->queue_number}");

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to join queue: ' . $e->getMessage());
        }
    }
}
