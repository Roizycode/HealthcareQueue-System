<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Service;
use App\Models\Priority;
use App\Models\Queue;
use App\Services\QueueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PatientController extends Controller
{
    protected QueueService $queueService;

    public function __construct(QueueService $queueService)
    {
        $this->queueService = $queueService;
    }

    /**
     * Register a new patient and join queue (Virtual Queue)
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => ['required', 'string', 'size:13', 'regex:/^\+639\d{9}$/', 'unique:patients,phone'],
            'email' => 'nullable|email|max:255|unique:patients,email',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'service_id' => 'required|exists:services,id',
            'priority_id' => 'required|exists:priorities,id',
            'reason_for_visit' => 'nullable|string|max:500',
            'is_senior' => 'boolean',
            'is_pwd' => 'boolean',
            'sms_notifications' => 'boolean',
        ], [
            'phone.regex' => 'Phone number must start with +639 and contain 13 characters.',
            'phone.size' => 'Phone number must be exactly 13 characters including +63.',
            'phone.unique' => 'This phone number is already registered.',
            'email.unique' => 'This email address is already registered.',
        ]);

        // Prevent multiple active tickets
        $activeQueue = Queue::whereDate('created_at', today())
            ->whereHas('patient', function($q) use ($validated) {
                $q->where('phone', $validated['phone']);
            })
            ->whereIn('status', ['waiting', 'called', 'serving'])
            ->exists();

        if ($activeQueue) {
            return back()->with('error', 'You already have an active queue ticket today. Please cancel it before joining again or check your status.__EXISTING_TICKET__')->withInput();
        }

        try {
            return DB::transaction(function () use ($validated) {
                // Find or create patient
                $patient = Patient::firstOrCreate(
                    ['phone' => $validated['phone']],
                    [
                        'first_name' => $validated['first_name'],
                        'last_name' => $validated['last_name'],
                        'email' => $validated['email'] ?? null,
                        'date_of_birth' => $validated['date_of_birth'] ?? null,
                        'gender' => $validated['gender'] ?? null,
                        'is_senior' => $validated['is_senior'] ?? false,
                        'is_pwd' => $validated['is_pwd'] ?? false,
                        'sms_notifications' => $validated['sms_notifications'] ?? true,
                    ]
                );

                // Update patient info if needed
                $patient->update([
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'is_senior' => $validated['is_senior'] ?? $patient->is_senior,
                    'is_pwd' => $validated['is_pwd'] ?? $patient->is_pwd,
                    'sms_notifications' => $validated['sms_notifications'] ?? $patient->sms_notifications,
                ]);

                $service = Service::findOrFail($validated['service_id']);
                $priority = Priority::findOrFail($validated['priority_id']);

                // Join queue
            $queue = $this->queueService->joinQueue(
                $patient,
                $service,
                $priority,
                'virtual',
                $validated['reason_for_visit'] ?? null
            );
            
            // Notify patient
            $patient->notify(new \App\Notifications\QueueStatusUpdated($queue, 'joined'));

            if (request()->ajax() || request()->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Successfully joined the queue!',
                        'data' => [
                            'queue_number' => $queue->queue_number,
                            'position' => $queue->position,
                            'estimated_wait_time' => $queue->estimated_wait_time,
                        ],
                    ]);
                }

                return redirect()->route('queue.status', ['queue' => $queue->queue_number])
                    ->with('swal_success', 'Successfully joined the queue!');
            });

        } catch (\Exception $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Walk-in patient registration by staff
     */
    public function walkInRegister(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => ['required', 'string', 'size:13', 'regex:/^\+639\d{9}$/', 'unique:patients,phone'],
            'email' => 'nullable|email|max:255|unique:patients,email',
            'service_id' => 'required|exists:services,id',
            'priority_id' => 'required|exists:priorities,id',
            'reason_for_visit' => 'nullable|string|max:500',
            'is_senior' => 'boolean',
            'is_pwd' => 'boolean',
        ], [
            'phone.regex' => 'Phone number must start with +639 and contain 13 characters.',
            'phone.size' => 'Phone number must be exactly 13 characters including +63.',
            'phone.unique' => 'This phone number is already registered.',
            'email.unique' => 'This email address is already registered.',
        ]);

        try {
            $queue = DB::transaction(function () use ($validated) {
                // Create or find patient
                $patient = Patient::firstOrCreate(
                    ['phone' => $validated['phone']],
                    [
                        'first_name' => $validated['first_name'],
                        'last_name' => $validated['last_name'],
                        'email' => $validated['email'] ?? null,
                        'is_senior' => $validated['is_senior'] ?? false,
                        'is_pwd' => $validated['is_pwd'] ?? false,
                    ]
                );

                $service = Service::findOrFail($validated['service_id']);
                $priority = Priority::findOrFail($validated['priority_id']);

                $queue = $this->queueService->joinQueue(
                    $patient,
                    $service,
                    $priority,
                    'walk_in',
                    $validated['reason_for_visit'] ?? null
                );

                // Notify patient (reload relations first)
                $queue->load('service', 'priority');
                $patient->notify(new \App\Notifications\QueueStatusUpdated($queue, 'joined'));

                // Notify Staff (Current User)
                if ($user = auth()->user()) {
                    $user->notify(new \App\Notifications\StaffAlert([
                        'title' => 'Registration Successful',
                        'message' => "Registered {$patient->full_name} to {$service->name}. Ticket: {$queue->queue_number}",
                        'type' => 'success',
                        'icon' => 'fas fa-user-plus'
                    ]));
                }

                return $queue;
            });

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Patient registered successfully',
                    'data' => [
                        'queue_number' => $queue->queue_number,
                        'patient_name' => $queue->patient->full_name,
                        'service' => $queue->service->name,
                    ]
                ]);
            }

            return view('staff.queue-success', compact('queue'));

        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Display patient's queue ticket
     */
    public function ticket(Queue $queue): View
    {
        $queue->load(['patient', 'service', 'priority']);
        $status = $this->queueService->getQueueStatus($queue);

        return view('queue.track', compact('queue', 'status'));
    }

    /**
     * Cancel patient's queue
     */
    public function cancelQueue(Queue $queue)
    {
        try {
            $this->queueService->cancelQueue($queue);

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Queue cancelled successfully.',
                ]);
            }

            return redirect()->route('home')->with('success', 'Queue cancelled successfully.');

        } catch (\Exception $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Get real-time queue status (API)
     */
    public function getStatus(Queue $queue)
    {
        $status = $this->queueService->getQueueStatus($queue);

        return response()->json([
            'success' => true,
            'data' => $status,
        ]);
    }

    /**
     * Search patients (for staff)
     */
    public function search(Request $request)
    {
        $request->validate([
            'term' => 'required|string|min:2|max:50',
        ]);

        $patients = Patient::search($request->input('term'))
            ->limit(10)
            ->get(['id', 'patient_id', 'first_name', 'last_name', 'phone', 'is_senior', 'is_pwd']);

        return response()->json([
            'success' => true,
            'data' => $patients->map(function ($patient) {
                return [
                    'id' => $patient->id,
                    'patient_id' => $patient->patient_id,
                    'name' => $patient->full_name,
                    'phone' => $patient->phone,
                    'is_senior' => $patient->is_senior,
                    'is_pwd' => $patient->is_pwd,
                    'has_active_queue' => $patient->hasActiveQueue(),
                ];
            }),
        ]);
    }

    /**
     * Process queue payment
     */
    public function processPayment(Queue $queue)
    {
        if ($queue->status !== 'completed') {
            return response()->json(['success' => false, 'message' => 'Service not completed yet.'], 403);
        }

        $queue->payment_status = 'paid';
        $queue->save();

        // Record Payment
        $queue->load('service'); // Ensure service is loaded to access price
        if ($queue->service->price > 0) {
            \App\Models\Payment::create([
                'queue_id' => $queue->id,
                'amount' => $queue->service->price,
                'payment_method' => 'GCash',
                'status' => 'paid'
            ]);
        }

        // Notify patient
        $queue->load('service', 'counter', 'priority'); 
        $queue->patient->notify(new \App\Notifications\QueueStatusUpdated($queue, 'payment_successful'));

        // Notify Staff
        if ($user = auth()->user()) {
            $user->notify(new \App\Notifications\StaffAlert([
                'title' => 'Payment Received',
                'message' => "Payment processed for Ticket {$queue->queue_number}.",
                'type' => 'success',
                'icon' => 'fas fa-cash-register'
            ]));
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment processed successfully!'
        ]);
    }
    /**
     * Print payment receipt
     */
    public function printReceipt(Queue $queue)
    {
        if ($queue->payment_status !== 'paid') {
            abort(403, 'Payment not completed or pending.');
        }

        return view('queue.receipt', compact('queue'));
    }
    /**
     * Show printable ticket
     */
    public function showTicket(Queue $queue)
    {
        return view('queue.ticket', compact('queue'));
    }
}
