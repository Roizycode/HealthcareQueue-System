<?php

namespace App\Http\Controllers;

use App\Models\Queue;
use App\Models\Service;
use App\Models\Priority;
use App\Models\Counter;
use App\Services\QueueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QueueController extends Controller
{
    protected QueueService $queueService;

    public function __construct(QueueService $queueService)
    {
        $this->queueService = $queueService;
    }

    /**
     * Get waiting list for a service
     */
    public function waitingList(Service $service)
    {
        $queues = $this->queueService->getWaitingList($service, 50);

        return response()->json([
            'success' => true,
            'data' => $queues->map(function ($queue) {
                return [
                    'id' => $queue->id,
                    'queue_number' => $queue->queue_number,
                    'patient_name' => $queue->patient->full_name,
                    'priority' => $queue->priority->name,
                    'priority_code' => $queue->priority->code,
                    'priority_color' => $queue->priority->color,
                    'status' => $queue->status,
                    'status_text' => $queue->status_text,
                    'wait_time' => $queue->formatted_wait_time,
                    'service' => $queue->service->name,
                    'position' => $queue->position,
                    'checked_in_at' => $queue->checked_in_at?->format('H:i'),
                ];
            }),
        ]);
    }

    /**
     * Call a specific queue (manual selection)
     */
    public function callSpecific(Request $request, Queue $queue)
    {
        $validated = $request->validate([
            'counter_id' => 'required|exists:counters,id',
        ]);

        try {
            $counter = Counter::findOrFail($validated['counter_id']);
            $userId = Auth::id() ?? 1;

            if ($queue->status !== 'waiting') {
                return response()->json([
                    'success' => false,
                    'message' => 'Queue is not waiting.',
                ], 422);
            }

            // Verify logic (e.g. is this queue for the right service?) - Skipped for flexibility
            
            $queue->call($counter->id, $userId);

            // Dispatch notification
            if ($queue->patient->sms_notifications) {
                dispatch(new \App\Jobs\NotifyPatientJob($queue, 'queue_called'));
            }

            return response()->json([
                'success' => true,
                'message' => "Calling {$queue->queue_number}",
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Call the next patient in queue (specific service)
     */
    public function callNext(Request $request, Service $service)
    {
        $validated = $request->validate([
            'counter_id' => 'required|exists:counters,id',
        ]);

        try {
            $counter = Counter::findOrFail($validated['counter_id']);
            $userId = Auth::id() ?? 1;

            $queue = $this->queueService->callNext($service, $counter, $userId);

            if (!$queue) {
                return response()->json([
                    'success' => false,
                    'message' => 'No patients waiting in queue.',
                ], 404);
            }

            // Notify patient
            $queue->patient->notify(new \App\Notifications\QueueStatusUpdated($queue, 'called'));

            return response()->json([
                'success' => true,
                'message' => "Calling {$queue->queue_number}",
                'data' => [
                    'id' => $queue->id,
                    'queue_number' => $queue->queue_number,
                    'patient_name' => $queue->patient->full_name,
                    'priority' => $queue->priority->name,
                    'counter' => $counter->name,
                    'service' => $service->name,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Call the next patient in queue (general/universal)
     * Uses counter's assigned service, or searches all services if counter is universal
     */
    public function callNextGeneral(Request $request)
    {
        $validated = $request->validate([
            'counter_id' => 'required|exists:counters,id',
        ]);

        try {
            $counter = Counter::findOrFail($validated['counter_id']);
            $userId = Auth::id() ?? 1;

            // Use counter's assigned service, or null (universal)
            $service = $counter->service;

            $queue = $this->queueService->callNext($service, $counter, $userId);

            if (!$queue) {
                return response()->json([
                    'success' => false,
                    'message' => 'No patients waiting in queue.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => "Calling {$queue->queue_number}",
                'data' => [
                    'id' => $queue->id,
                    'queue_number' => $queue->queue_number,
                    'patient_name' => $queue->patient->full_name,
                    'priority' => $queue->priority->name,
                    'counter' => $counter->name,
                    'service' => $queue->service->name, // Return actual service name
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Recall a patient
     */
    public function recall(Queue $queue)
    {
        try {
            $this->queueService->recallPatient($queue);

            return response()->json([
                'success' => true,
                'message' => "Recalling {$queue->queue_number}",
                'data' => [
                    'queue_number' => $queue->queue_number,
                    'recall_count' => $queue->recall_count,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Start serving a patient
     */
    public function startServing(Queue $queue)
    {
        try {
            $this->queueService->startServing($queue);

            // Notify patient
            $queue->patient->notify(new \App\Notifications\QueueStatusUpdated($queue, 'serving'));

            return response()->json([
                'success' => true,
                'message' => "Now serving {$queue->queue_number}",
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Complete a queue
     */
    public function complete(Queue $queue)
    {
        try {
            // Validate that service has been started
            if (!$queue->serving_started_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot complete service. Please start the service first.',
                ], 422);
            }

            $this->queueService->completeQueue($queue);

            // Notify patient - Service Completed
            $queue->patient->notify(new \App\Notifications\QueueStatusUpdated($queue, 'completed'));
            
            // Notify patient - Payment Required (if status is completed)
            if ($queue->refresh()->status === 'completed' && $queue->payment_status === 'pending') {
                 $queue->patient->notify(new \App\Notifications\QueueStatusUpdated($queue, 'payment_required'));
            }

            return response()->json([
                'success' => true,
                'message' => "Queue {$queue->queue_number} completed",
                'data' => [
                    'service_duration' => $queue->service_duration,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Skip a patient (no show)
     */
    public function skip(Queue $queue)
    {
        try {
            $this->queueService->skipPatient($queue);

            // Notify patient
            $queue->patient->notify(new \App\Notifications\QueueStatusUpdated($queue, 'skipped'));

            // Notify Staff
            if ($user = auth()->user()) {
                $user->notify(new \App\Notifications\StaffAlert([
                    'title' => 'Queue Skipped',
                    'message' => "You skipped Ticket {$queue->queue_number}.",
                    'type' => 'warning',
                    'icon' => 'fas fa-forward'
                ]));
            }

            return response()->json([
                'success' => true,
                'message' => "Queue {$queue->queue_number} skipped",
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Cancel a queue
     */
    public function cancel(Queue $queue)
    {
        try {
            $this->queueService->cancelQueue($queue);

            // Notify patient
            $queue->patient->notify(new \App\Notifications\QueueStatusUpdated($queue, 'cancelled'));

            // Notify Staff
            if ($user = auth()->user()) {
                $user->notify(new \App\Notifications\StaffAlert([
                    'title' => 'Queue Cancelled',
                    'message' => "You cancelled Ticket {$queue->queue_number}.",
                    'type' => 'danger',
                    'icon' => 'fas fa-ban'
                ]));
            }

            return response()->json([
                'success' => true,
                'message' => "Queue {$queue->queue_number} cancelled",
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get service statistics
     */
    public function serviceStats(Service $service)
    {
        $stats = $this->queueService->getServiceStats($service);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get overall statistics
     */
    public function overallStats()
    {
        $stats = $this->queueService->getOverallStats();

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get current serving for all services
     */
    public function currentServing()
    {
        $services = Service::active()->ordered()->get();

        $data = $services->map(function ($service) {
            $serving = Queue::where('service_id', $service->id)
                ->where('status', 'serving')
                ->whereDate('created_at', today())
                ->with(['counter', 'patient'])
                ->first();

            return [
                'service_id' => $service->id,
                'service_name' => $service->name,
                'service_code' => $service->code,
                'serving' => $serving ? [
                    'queue_number' => $serving->queue_number,
                    'patient_name' => $serving->patient->first_name,
                    'counter' => $serving->counter?->name,
                ] : null,
                'waiting_count' => $service->waiting_count,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
