<?php

namespace App\Services;

use App\Models\Queue;
use App\Models\Service;
use App\Models\Patient;
use App\Models\Priority;
use App\Models\Counter;
use App\Models\QueueSetting;
use App\Jobs\NotifyPatientJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class QueueService
{
    /**
     * Join a patient to a queue
     */
    public function joinQueue(
        Patient $patient,
        Service $service,
        Priority $priority,
        string $queueType = 'walk_in',
        ?string $reasonForVisit = null
    ): Queue {
        // Check if patient already has active queue for this service
        if ($this->hasActiveQueue($patient, $service)) {
            throw new \Exception('Patient already has an active queue for this service.');
        }

        // Check if service queue is full
        if ($service->isQueueFull()) {
            throw new \Exception('Queue is currently full. Please try again later.');
        }

        // Check Max Queue Size Daily
        $maxDaily = (int) QueueSetting::get('max_queue_size', 200);
        $currentTotal = Queue::whereDate('created_at', today())->count();
        if ($currentTotal >= $maxDaily) {
            throw new \Exception('Daily queue limit reached. Please try again tomorrow.');
        }

        return DB::transaction(function () use ($patient, $service, $priority, $queueType, $reasonForVisit) {
            $queue = Queue::create([
                'patient_id' => $patient->id,
                'service_id' => $service->id,
                'priority_id' => $priority->id,
                'queue_number' => $service->generateQueueNumber(),
                'queue_type' => $queueType,
                'status' => 'waiting',
                'checked_in_at' => now(),
                'reason_for_visit' => $reasonForVisit,
                'estimated_wait_time' => $this->calculateEstimatedWaitTime($service, $priority),
            ]);

            // Dispatch notification for queue joined
            if ($patient->sms_notifications) {
                dispatch(new NotifyPatientJob($queue, 'queue_joined'));
            }

            return $queue;
        });
    }

    /**
     * Check if patient has active queue for a service
     */
    public function hasActiveQueue(Patient $patient, ?Service $service = null): bool
    {
        $query = Queue::where('patient_id', $patient->id)
            ->whereIn('status', ['waiting', 'called', 'serving'])
            ->whereDate('created_at', today());

        if ($service) {
            $query->where('service_id', $service->id);
        }

        return $query->exists();
    }

    /**
     * Get the next queue to be called for a service
     * Priority first, then FIFO within priority
     */
    public function getNextQueue(Service $service): ?Queue
    {
        return Queue::where('queues.service_id', $service->id)
            ->where('queues.status', 'waiting')
            ->whereDate('queues.created_at', today())
            ->join('priorities', 'queues.priority_id', '=', 'priorities.id')
            ->orderBy('priorities.level', 'desc')
            ->orderBy('queues.created_at', 'asc')
            ->select('queues.*')
            ->first();
    }

    /**
     * Get the next queue across ALL services
     */
    public function getNextQueueUniversal(): ?Queue
    {
        return Queue::where('queues.status', 'waiting')
            ->whereDate('queues.created_at', today())
            ->join('priorities', 'queues.priority_id', '=', 'priorities.id')
            ->orderBy('priorities.level', 'desc')
            ->orderBy('queues.created_at', 'asc')
            ->select('queues.*')
            ->first();
    }

    /**
     * Call the next patient
     */
    public function callNext(?Service $service, Counter $counter, int $calledBy): ?Queue
    {
        return DB::transaction(function () use ($service, $counter, $calledBy) {
            if ($service) {
                $queue = $this->getNextQueue($service);
            } else {
                $queue = $this->getNextQueueUniversal();
            }

            if (!$queue) {
                return null;
            }

            $queue->call($counter->id, $calledBy);

            // Dispatch notification (Safely)
            if ($queue->patient->sms_notifications) {
                try {
                    dispatch(new NotifyPatientJob($queue, 'queue_called'));
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('Notification failed: ' . $e->getMessage());
                }
            }

            // Check and notify patients who are now near
            try {
                $this->notifyNearPatients($queue->service);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Near Notifications failed: ' . $e->getMessage());
            }

            return $queue;
        });
    }

    /**
     * Recall a patient (call again)
     */
    public function recallPatient(Queue $queue): Queue
    {
        $maxRecalls = QueueSetting::get('max_recall_attempts', 3);

        if ($queue->recall_count >= $maxRecalls) {
            throw new \Exception('Maximum recall attempts reached. Consider skipping this patient.');
        }

        $queue->recall_count++;
        $queue->called_at = now();
        $queue->save();

        // Send notification again
        if ($queue->patient->sms_notifications) {
            try {
                dispatch(new NotifyPatientJob($queue, 'queue_called'));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Recall Notification failed: ' . $e->getMessage());
            }
        }

        return $queue;
    }

    /**
     * Start serving a patient
     */
    public function startServing(Queue $queue): Queue
    {
        if (!$queue->canBeServed()) {
            throw new \Exception('Queue cannot be served in current status.');
        }

        $queue->startServing();
        return $queue;
    }

    /**
     * Complete a queue
     */
    public function completeQueue(Queue $queue): Queue
    {
        if (!$queue->canBeCompleted()) {
            throw new \Exception('Queue cannot be completed in current status.');
        }

        return DB::transaction(function () use ($queue) {
            $queue->complete();
            
            // Set payment status to pending as service is completed
            $queue->payment_status = 'pending';
            $queue->save();

            // Dispatch completion notification
            if ($queue->patient->sms_notifications) {
                try {
                    dispatch(new NotifyPatientJob($queue, 'queue_completed'));
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('Completion Notification failed: ' . $e->getMessage());
                }
            }

            return $queue;
        });
    }

    /**
     * Skip a patient (no show)
     */
    public function skipPatient(Queue $queue): Queue
    {
        $queue->skip();
        return $queue;
    }

    /**
     * Cancel a queue
     */
    public function cancelQueue(Queue $queue): Queue
    {
        $queue->cancel();

        // Notify patient of cancellation
        if ($queue->patient->sms_notifications) {
            try {
                dispatch(new NotifyPatientJob($queue, 'queue_cancelled'));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Cancellation Notification failed: ' . $e->getMessage());
            }
        }

        return $queue;
    }

    /**
     * Notify patients who are near (3 positions away by default)
     */
    public function notifyNearPatients(Service $service): void
    {
        $threshold = QueueSetting::get('queue_near_threshold', 3);

        $nearQueues = Queue::where('service_id', $service->id)
            ->where('status', 'waiting')
            ->where('near_notification_sent', false)
            ->whereDate('created_at', today())
            ->get();

        foreach ($nearQueues as $queue) {
            $position = $queue->position;

            if ($position <= $threshold) {
                // Notify via Web/Email
                try {
                    $queue->patient->notify(new \App\Notifications\QueueStatusUpdated($queue, 'next'));
                } catch (\Throwable $e) {
                     \Illuminate\Support\Facades\Log::error('Near Web Notification failed: ' . $e->getMessage());
                }

                // Notify via SMS if enabled
                if ($queue->patient->sms_notifications) {
                    try {
                        dispatch(new NotifyPatientJob($queue, 'queue_near'));
                    } catch (\Throwable $e) {
                         \Illuminate\Support\Facades\Log::error('Near SMS Notification failed: ' . $e->getMessage());
                    }
                }
                
                $queue->near_notification_sent = true;
                $queue->save();
            }
        }
    }

    /**
     * Check and escalate queues exceeding wait time threshold
     */
    public function checkAndEscalate(): Collection
    {
        if (!QueueSetting::get('escalation_enabled', true)) {
            return collect();
        }

        $escalatedQueues = collect();

        $queues = Queue::where('status', 'waiting')
            ->where('was_escalated', false)
            ->whereDate('created_at', today())
            ->with('priority')
            ->get();

        foreach ($queues as $queue) {
            $maxWaitTime = $queue->priority->max_wait_time ?? 60;
            $waitTime = $queue->created_at->diffInMinutes(now());

            if ($waitTime > $maxWaitTime) {
                $queue->escalate();
                $escalatedQueues->push($queue);
            }
        }

        return $escalatedQueues;
    }

    /**
     * Calculate estimated wait time for a new queue entry
     */
    public function calculateEstimatedWaitTime(Service $service, Priority $priority): int
    {
        // Count waiting queues ahead based on priority
        $aheadCount = Queue::where('service_id', $service->id)
            ->where('status', 'waiting')
            ->whereDate('created_at', today())
            ->where(function ($query) use ($priority) {
                $query->whereHas('priority', function ($q) use ($priority) {
                    $q->where('level', '>', $priority->level);
                });
            })
            ->count();

        // Add queues with same priority that are ahead
        $aheadCount += Queue::where('service_id', $service->id)
            ->where('status', 'waiting')
            ->whereDate('created_at', today())
            ->where('priority_id', $priority->id)
            ->count();

        $averageServiceTime = $service->average_service_time ?? 15;
        $openCounters = max(1, $service->counters()->open()->count());

        // Check if all counters are busy
        $servingCount = Queue::where('service_id', $service->id)
            ->where('status', 'serving')
            ->whereDate('created_at', today())
            ->count();

        $waitTime = ($aheadCount * $averageServiceTime) / $openCounters;

        // If counters are full (serving >= open), we must wait for one to finish
        // Add one cycle of service time distributed across counters
        if ($servingCount >= $openCounters) {
            $waitTime += ($averageServiceTime / $openCounters);
        }

        return (int) ceil($waitTime);
    }

    /**
     * Get waiting list for a service ordered by priority
     */
    public function getWaitingList(Service $service, int $limit = 50): Collection
    {
        // Add table qualifiers to avoid ambiguity with joined tables
        return Queue::where('queues.service_id', $service->id)
            ->whereIn('queues.status', ['waiting', 'called'])
            ->whereDate('queues.created_at', today())
            ->with(['patient', 'priority'])
            ->join('priorities', 'queues.priority_id', '=', 'priorities.id')
            ->orderBy('priorities.level', 'desc')
            ->orderBy('queues.created_at', 'asc')
            ->orderBy('queues.id', 'asc') // Stabilize sort
            ->select('queues.*')
            ->limit($limit)
            ->get();
    }

    /**
     * Get queue statistics for a service
     */
    public function getServiceStats(Service $service): array
    {
        $today = today();

        $waiting = Queue::where('service_id', $service->id)
            ->where('status', 'waiting')
            ->whereDate('created_at', $today)
            ->count();

        $serving = Queue::where('service_id', $service->id)
            ->where('status', 'serving')
            ->whereDate('created_at', $today)
            ->count();

        $completed = Queue::where('service_id', $service->id)
            ->where('status', 'completed')
            ->whereDate('created_at', $today)
            ->count();

        $skipped = Queue::where('service_id', $service->id)
            ->where('status', 'skipped')
            ->whereDate('created_at', $today)
            ->count();

        $avgWaitTime = Queue::where('service_id', $service->id)
            ->where('status', 'completed')
            ->whereDate('created_at', $today)
            ->whereNotNull('actual_wait_time')
            ->avg('actual_wait_time') ?? 0;

        $avgServiceTime = Queue::where('service_id', $service->id)
            ->where('status', 'completed')
            ->whereDate('created_at', $today)
            ->whereNotNull('service_duration')
            ->avg('service_duration') ?? 0;

        return [
            'waiting' => $waiting,
            'serving' => $serving,
            'completed' => $completed,
            'skipped' => $skipped,
            'total_today' => $waiting + $serving + $completed + $skipped,
            'average_wait_time' => round($avgWaitTime),
            'average_service_time' => round($avgServiceTime),
        ];
    }

    /**
     * Get overall statistics for dashboard
     */
    public function getOverallStats(): array
    {
        $today = today();

        return [
            'total_waiting' => Queue::where('status', 'waiting')->whereDate('created_at', $today)->count(),
            'total_serving' => Queue::where('status', 'serving')->whereDate('created_at', $today)->count(),
            'total_completed' => Queue::where('status', 'completed')->whereDate('created_at', $today)->count(),
            'total_skipped' => Queue::where('status', 'skipped')->whereDate('created_at', $today)->count(),
            'total_cancelled' => Queue::where('status', 'cancelled')->whereDate('created_at', $today)->count(),
            'average_wait_time' => round(
                Queue::where('status', 'completed')
                    ->whereDate('created_at', $today)
                    ->whereNotNull('actual_wait_time')
                    ->avg('actual_wait_time') ?? 0
            ),
            'services' => Service::active()->get()->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'code' => $service->code,
                    'waiting' => $service->waiting_count,
                    'current_serving' => $service->current_serving?->queue_number,
                ];
            }),
        ];
    }

    /**
     * Get queue status for patient portal
     */
    public function getQueueStatus(Queue $queue): array
    {
        return [
            'queue_number' => $queue->queue_number,
            'status' => $queue->status,
            'status_text' => $queue->status_text,
            'position' => $queue->position,
            'estimated_wait_time' => $queue->calculateEstimatedWaitTime(),
            'wait_time' => $queue->wait_time,
            'formatted_wait_time' => $queue->formatted_wait_time,
            'service' => $queue->service->name,
            'priority' => $queue->priority->name,
            'counter' => $queue->counter?->name,
            'checked_in_at' => $queue->checked_in_at?->format('H:i'),
            'called_at' => $queue->called_at?->format('H:i'),
        ];
    }
}
