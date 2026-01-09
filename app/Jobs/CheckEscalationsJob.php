<?php

namespace App\Jobs;

use App\Services\QueueService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckEscalationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 1;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     * This job checks for queues that have exceeded their waiting threshold
     * and escalates their priority.
     */
    public function handle(QueueService $queueService): void
    {
        try {
            Log::info('Checking for queue escalations...');

            $escalatedQueues = $queueService->checkAndEscalate();

            if ($escalatedQueues->isNotEmpty()) {
                Log::info("Escalated {$escalatedQueues->count()} queues");
                
                foreach ($escalatedQueues as $queue) {
                    Log::info("Queue {$queue->queue_number} escalated to emergency priority");
                }
            }

        } catch (\Exception $e) {
            Log::error('Escalation check failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return ['escalation', 'queue-maintenance'];
    }
}
