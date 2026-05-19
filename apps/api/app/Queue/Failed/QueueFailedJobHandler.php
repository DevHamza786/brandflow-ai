<?php

declare(strict_types=1);

namespace App\Queue\Failed;

use App\Queue\Jobs\AbstractQueueJob;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Central failed-job handler (logging, alerts for critical queues).
 */
final class QueueFailedJobHandler
{
    public function handle(AbstractQueueJob $job, ?Throwable $exception): void
    {
        $context = [
            'job' => $job::class,
            'queue' => $job->queueName(),
            'workspace_id' => $job->workspaceId,
            'tags' => $job->tags(),
            'attempts' => $job->attempts(),
            'message' => $exception?->getMessage(),
        ];

        Log::channel(config('queues.failed.log_channel', 'stack'))
            ->error('queue.job.failed', $context);

        if (in_array($job->queueName(), config('queues.failed.alert_on_queues', []), true)) {
            Log::channel(config('queues.failed.log_channel', 'stack'))
                ->critical('queue.job.failed.alert', $context);
        }
    }
}
