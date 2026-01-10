<?php

namespace App\Http\Controllers;

use App\Models\Queue;
use App\Models\Service;
use App\Models\Counter;
use App\Models\Priority;
use App\Services\QueueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StaffDashboardController extends Controller
{
    protected QueueService $queueService;

    public function __construct(QueueService $queueService)
    {
        $this->queueService = $queueService;
    }

    /**
     * Display the staff dashboard
     */
    public function index(): View
    {
        $user = Auth::user();
        
        // Show all services for filtering options (regardless of assignment)
        $services = Service::active()->ordered()->get();

        // Get all active counters (allow calling to any counter)
        $counters = Counter::active()
            ->with(['service'])
            ->get();

        $priorities = Priority::active()->byLevel()->get();

        // Current serving by staff
        $currentServing = $user ? Queue::where('called_by', $user->id)
            ->whereIn('status', ['called', 'serving'])
            ->whereDate('created_at', today())
            ->with(['patient', 'service', 'counter'])
            ->first() : null;

        return view('staff.dashboard', compact(
            'services',
            'counters',
            'priorities',
            'currentServing'
        ));
    }

    /**
     * Get waiting list for staff view
     */
    public function getWaitingList(Request $request)
    {
        $serviceId = $request->input('service_id');

        $query = Queue::where('queues.status', 'waiting')
            ->whereDate('queues.created_at', today())
            ->with(['patient', 'service', 'priority']);

        if ($serviceId) {
            $query->where('queues.service_id', $serviceId);
        }

        $queues = $query->join('priorities', 'queues.priority_id', '=', 'priorities.id')
            ->orderBy('priorities.level', 'desc')
            ->orderBy('queues.created_at', 'asc')
            ->orderBy('queues.id', 'asc')
            ->select('queues.*')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $queues->map(function ($queue) {
                return [
                    'id' => $queue->id,
                    'queue_number' => $queue->queue_number,
                    'patient_name' => $queue->patient?->full_name ?? 'Guest',
                    'patient_phone' => $queue->patient?->phone ?? '-',
                    'service' => $queue->service?->name ?? 'Unknown',
                    'service_code' => $queue->service?->code ?? 'UNK',
                    'priority' => $queue->priority?->name ?? 'Normal',
                    'priority_name' => $queue->priority?->name ?? 'Regular',
                    'priority_code' => $queue->priority?->code ?? 'REG',
                    'priority_color' => $queue->priority?->color ?? '#6c757d',
                    'status' => $queue->status,
                    'wait_time' => $queue->created_at->diffInMinutes(now()),
                    'joined_time' => $queue->created_at->format('g:i A'),
                    'checked_in_at' => $queue->checked_in_at?->format('H:i') ?? '-',
                    'recall_count' => $queue->recall_count,
                ];
            }),
        ]);
    }

    /**
     * Get called queues for staff view
     */
    public function getCalledQueues(Request $request)
    {
        $query = Queue::where('status', 'called')
            ->whereDate('created_at', today())
            ->with(['patient', 'service', 'priority', 'counter']);

        if ($serviceId = $request->input('service_id')) {
            $query->where('service_id', $serviceId);
        }

        $queues = $query->orderBy('called_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $queues->map(function ($queue) {
                return [
                    'id' => $queue->id,
                    'queue_number' => $queue->queue_number,
                    'patient_name' => $queue->patient?->full_name ?? 'Guest',
                    'service' => $queue->service?->name ?? 'Unknown',
                    'priority' => $queue->priority?->name ?? 'Normal',
                    'priority_color' => $queue->priority?->color ?? '#6c757d',
                    'counter' => $queue->counter?->name ?? '-',
                    'called_at' => $queue->called_at?->format('g:i A') ?? '-',
                    'recall_count' => $queue->recall_count,
                ];
            }),
        ]);
    }

    /**
     * Get serving queues for staff view
     */
    public function getServingQueues(Request $request)
    {
        $query = Queue::whereIn('status', ['serving', 'called'])
            ->whereDate('created_at', today())
            ->with(['patient', 'service', 'priority', 'counter', 'calledByUser']);

        if ($serviceId = $request->input('service_id')) {
            $query->where('service_id', $serviceId);
        }

        $queues = $query->orderBy('serving_started_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $queues->map(function ($queue) {
                return [
                    'id' => $queue->id,
                    'queue_number' => $queue->queue_number,
                    'patient_name' => $queue->patient?->full_name ?? 'Guest',
                    'service' => $queue->service?->name ?? 'Unknown',
                    'priority' => $queue->priority?->name ?? 'Normal',
                    'counter' => $queue->counter?->name ?? '-',
                    'status' => $queue->status,
                    'called_by' => $queue->calledByUser?->name ?? 'Staff',
                    'serving_started_at' => $queue->serving_started_at?->format('g:i A') ?? null,
                    'service_duration' => $queue->serving_started_at?->diffInMinutes(now()) ?? 0,
                ];
            }),
        ]);
    }

    /**
     * Quick stats for staff dashboard
     */
    public function quickStats(Request $request)
    {
        $serviceId = $request->input('service_id');

        $query = Queue::whereDate('created_at', today());

        if ($serviceId) {
            $query->where('service_id', $serviceId);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'waiting' => (clone $query)->where('status', 'waiting')->count(),
                'called' => (clone $query)->where('status', 'called')->count(),
                'serving' => (clone $query)->where('status', 'serving')->count(),
                'completed' => (clone $query)->where('status', 'completed')->count(),
                'skipped' => (clone $query)->where('status', 'skipped')->count(),
            ],
        ]);
    }

    /**
     * Get current serving queue for the logged-in staff
     */
    public function getCurrentServing(Request $request)
    {
        $user = Auth::user();
        
        $currentServing = Queue::where('called_by', $user->id)
            ->whereIn('status', ['serving', 'called'])
            ->whereDate('created_at', today())
            ->with(['patient', 'service', 'counter'])
            ->orderBy('updated_at', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if (!$currentServing) {
            return response()->json([
                'success' => true,
                'data' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $currentServing->id,
                'queue_number' => $currentServing->queue_number,
                'patient_name' => $currentServing->patient->full_name,
                'duration' => $currentServing->serving_started_at?->diffInMinutes(now()) ?? 0,
                'serving_started_at' => $currentServing->serving_started_at ? true : false,
                'status' => $currentServing->status,
            ],
        ]);
    }

    /**
     * Get completed queues for today
     */
    public function getCompleted(Request $request)
    {
        $serviceId = $request->input('service_id');
        $limit = $request->input('limit', 10);

        $query = Queue::where('status', 'completed')
            ->whereDate('created_at', today())
            ->with(['patient', 'service', 'counter']);

        if ($serviceId) {
             $query->where('service_id', $serviceId);
        }

        $completed = $query->latest('completed_at')->limit($limit)->get();

        return response()->json([
            'success' => true,
            'data' => $completed->map(function ($queue) {
                return [
                    'id' => $queue->id,
                    'queue_number' => $queue->queue_number,
                    'patient_name' => $queue->patient->full_name,
                    'service_name' => $queue->service->name,
                    'completed_at' => $queue->completed_at ? $queue->completed_at->format('g:i A') : '-',
                    'counter' => $queue->counter ? $queue->counter->name : '-',
                    'wait_time' => $queue->checked_in_at && $queue->serving_started_at 
                        ? $queue->checked_in_at->diffInMinutes($queue->serving_started_at) : 0,
                    'duration' => $queue->serving_started_at && $queue->completed_at
                        ? $queue->serving_started_at->diffInMinutes($queue->completed_at) : 0,
                    'payment_status' => $queue->payment_status,
                ];
            })
        ]);
    }

    /**
     * Display patients list
     */
    public function patients(Request $request): View
    {
        $query = \App\Models\Patient::query()
            ->withCount('queues')
            ->with(['queues' => function($q) {
                $q->orderBy('created_at', 'desc')->limit(5);
            }]);

        if ($search = $request->input('search')) {
            $query->search($search);
        }

        $patients = $query->orderBy('created_at', 'desc')->paginate(25);
        $services = Service::active()->ordered()->get();
        $priorities = Priority::active()->byLevel()->get();

        return view('staff.patients', compact('patients', 'services', 'priorities'));
    }

    /**
     * Display add patient form
     */
    public function addPatient(): View
    {
        $services = Service::active()->ordered()->get();
        $priorities = Priority::active()->byLevel()->get();

        return view('staff.patients-add', compact('services', 'priorities'));
    }

    /**
     * Display priority queue management
     */
    public function priorityQueue(): View
    {
        $priorities = Priority::active()->byLevel()->get();
        return view('staff.priority-queue', compact('priorities'));
    }

    /**
     * Display service queue
     */
    public function serviceQueue(Service $service): View
    {
        $services = Service::active()->ordered()->get();
        $priorities = Priority::active()->byLevel()->get();
        $counters = Counter::active()->where('service_id', $service->id)->get();
        
        $waiting = Queue::where('service_id', $service->id)
            ->where('status', 'waiting')
            ->whereDate('created_at', today())
            ->with(['patient', 'priority'])
            ->orderBy('created_at')
            ->get();
            
        $serving = Queue::where('service_id', $service->id)
            ->whereIn('status', ['called', 'serving'])
            ->whereDate('created_at', today())
            ->with(['patient', 'counter'])
            ->get();
            
        $completed = Queue::where('service_id', $service->id)
            ->where('status', 'completed')
            ->whereDate('created_at', today())
            ->with(['patient'])
            ->orderByDesc('completed_at')
            ->limit(10)
            ->get();

        return view('staff.service-queue', compact('service', 'services', 'priorities', 'counters', 'waiting', 'serving', 'completed'));
    }

    /**
     * Display notifications
     */
    public function notifications(): View
    {
        $notifications = Auth::user()->notifications()->latest()->paginate(20);
        return view('staff.notifications', compact('notifications'));
    }

    /**
     * Fetch notifications for polling
     */
    public function fetchNotifications()
    {
        $notifications = Auth::user()->notifications()->latest()->limit(10)->get();
        $unreadCount = Auth::user()->unreadNotifications()->count();
        
        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ]);
    }

    /**
     * Display reports
     */
    public function reports(Request $request): View
    {
        $date = $request->input('date', today());
        
        // 1. Queue Performance
        $performance = Queue::whereDate('created_at', $date)
            ->where('status', 'completed')
            ->with(['service'])
            ->get()
            ->groupBy(fn($q) => $q->service->name)
            ->map(function ($queues) {
                return [
                    'served' => $queues->count(),
                    'avg_wait' => round($queues->avg('actual_wait_time') ?? 0),
                    'avg_service' => round($queues->avg('service_duration') ?? 0),
                ];
            });

        // 2. Service Utilization
        $totalCompleted = Queue::whereDate('created_at', $date)->where('status', 'completed')->count();
        $utilization = Queue::whereDate('created_at', $date)
            ->where('status', 'completed')
            ->with('service')
            ->get()
            ->groupBy(fn($q) => $q->service->name)
            ->map(function ($queues) use ($totalCompleted) {
                return [
                    'count' => $queues->count(),
                    'percentage' => $totalCompleted > 0 ? round(($queues->count() / $totalCompleted) * 100, 1) : 0,
                ];
            });

        // 3. Patient Visit Report (Recent Activity)
        $patientVisits = Queue::with('patient')
            ->selectRaw('patient_id, count(*) as visits, max(created_at) as last_visit')
            ->groupBy('patient_id')
            ->orderByDesc('last_visit')
            ->limit(20)
            ->get();

        // 4. Payment & Revenue
        $revenues = \App\Models\Payment::whereDate('created_at', $date)
            ->with(['queue.service'])
            ->get()
            ->groupBy(fn($p) => $p->queue->service->name)
            ->map(function ($payments) {
                return [
                    'tickets' => $payments->count(),
                    'amount' => $payments->sum('amount'),
                    'method' => $payments->unique('payment_method')->pluck('payment_method')->implode(', '),
                ];
            });

        // 5. Priority / Category Report
        $priorities = Queue::whereDate('created_at', $date)
            ->with('priority')
            ->get()
            ->groupBy(fn($q) => $q->priority->name)
            ->map(function ($queues) {
                return [
                    'count' => $queues->count(),
                    'avg_wait' => round($queues->avg('actual_wait_time') ?? 0),
                ];
            });

        return view('staff.reports', compact('performance', 'utilization', 'patientVisits', 'revenues', 'priorities', 'date'));
    }

    /**
     * Staff settings
     */
    public function settingsStaff(): View
    {
        return view('staff.settings-staff');
    }

    /**
     * Queue settings
     */
    public function settingsQueue(): View
    {
        return view('staff.settings-queue');
    }

    /**
     * SMS settings
     */
    public function settingsSms(): View
    {
        return view('staff.settings-sms');
    }

    /**
     * Display appointment requests
     */
    public function appointmentRequests(): View
    {
        $pendingRequests = \App\Models\AppointmentRequest::with(['patient', 'service'])
            ->where('status', 'pending')
            ->orderBy('preferred_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();
        
        $handledRequests = \App\Models\AppointmentRequest::with(['patient', 'service', 'handler'])
            ->whereIn('status', ['approved', 'rejected', 'cancelled'])
            ->orderBy('handled_at', 'desc')
            ->limit(20)
            ->get();
        
        return view('staff.appointment-requests', compact('pendingRequests', 'handledRequests'));
    }

    /**
     * Approve an appointment request
     */
    public function approveAppointment(Request $request, $id)
    {
        $appointmentRequest = \App\Models\AppointmentRequest::with(['patient.user', 'service'])->findOrFail($id);
        
        $validated = $request->validate([
            'staff_notes' => 'nullable|string|max:500',
        ]);
        
        $appointmentRequest->update([
            'status' => 'approved',
            'handled_by' => Auth::id(),
            'staff_notes' => $validated['staff_notes'] ?? null,
            'handled_at' => now(),
        ]);
        
        // Send email notification to patient
        try {
            if ($appointmentRequest->patient->user) {
                $appointmentRequest->patient->user->notify(
                    new \App\Notifications\AppointmentRequestStatus($appointmentRequest, 'approved')
                );
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send appointment approval email: ' . $e->getMessage());
        }
        
        return back()->with('success', 'Appointment request approved. Patient has been notified via email.');
    }

    /**
     * Reject an appointment request
     */
    public function rejectAppointment(Request $request, $id)
    {
        $appointmentRequest = \App\Models\AppointmentRequest::with(['patient.user', 'service'])->findOrFail($id);
        
        $validated = $request->validate([
            'staff_notes' => 'required|string|max:500',
        ]);
        
        $appointmentRequest->update([
            'status' => 'rejected',
            'handled_by' => Auth::id(),
            'staff_notes' => $validated['staff_notes'],
            'handled_at' => now(),
        ]);
        
        // Send email notification to patient
        try {
            if ($appointmentRequest->patient->user) {
                $appointmentRequest->patient->user->notify(
                    new \App\Notifications\AppointmentRequestStatus($appointmentRequest, 'rejected')
                );
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send appointment rejection email: ' . $e->getMessage());
        }
        
        return back()->with('success', 'Appointment request rejected. Patient has been notified via email.');
    }
    public function liveDisplayPage()
    {
        return view('staff.live-display');
    }

    /**
     * Get flat live queue data for staff display
     */
    public function getLiveQueueData()
    {
        // Get now serving
        $nowServing = \App\Models\Queue::whereIn('status', ['serving', 'called'])
            ->whereDate('created_at', today())
            ->with(['service', 'counter'])
            ->orderBy('called_at', 'desc')
            ->limit(10) // Limit slightly higher for staff view
            ->get()
            ->map(fn($q) => [
                'queue_number' => $q->queue_number,
                'service' => $q->service->name,
                'counter' => $q->counter?->name ?? 'Counter',
                'status' => $q->status,
                'patient_id' => $q->patient_id, // include for consistency
            ]);
        
        // Get waiting
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
}
