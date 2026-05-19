<?php

declare(strict_types=1);

namespace App\Queue\Middleware;

use App\Queue\Jobs\AbstractQueueJob;
use App\Queue\Support\RetryPolicyResolver;
use Closure;

/**
 * Ensures retry/timeout from config are applied before the job is handled.
 */
final class ApplyRetryPolicy
{
    public function __construct(
        private readonly RetryPolicyResolver $resolver,
    ) {
    }

    public function handle(AbstractQueueJob $job, Closure $next): mixed
    {
        $policy = $this->resolver->forQueue($job->queueName());

        $job->tries = $policy->tries;
        $job->backoff = $policy->backoff;
        $job->timeout = $policy->timeout;

        return $next($job);
    }
}
