<?php

declare(strict_types=1);

namespace App\Queue\Middleware;

use App\Queue\Jobs\AbstractQueueJob;
use Closure;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Structured logging for queue job lifecycle (start / fail).
 */
final class LogJobLifecycle
{
    public function handle(AbstractQueueJob $job, Closure $next): mixed
    {
        Log::debug('queue.job.started', [
            'job' => $job::class,
            'queue' => $job->queueName(),
            'workspace_id' => $job->workspaceId,
            'tags' => $job->tags(),
        ]);

        try {
            return $next($job);
        } catch (Throwable $exception) {
            Log::warning('queue.job.exception', [
                'job' => $job::class,
                'queue' => $job->queueName(),
                'workspace_id' => $job->workspaceId,
                'attempt' => $job->attempts(),
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
