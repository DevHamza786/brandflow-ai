<?php

declare(strict_types=1);

namespace App\Queue\Jobs;

use App\Queue\Jobs\Concerns\HasQueueTags;
use App\Queue\Middleware\ApplyRetryPolicy;
use App\Queue\Middleware\LogJobLifecycle;
use App\Queue\Support\RetryPolicyResolver;
use App\Queue\Failed\QueueFailedJobHandler;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * Base PBOS queued job: retry policy, timeout, tagging, failed-job handling.
 *
 * Domain jobs should extend this class (or AbstractWorkflowJob).
 */
abstract class AbstractQueueJob implements ShouldQueue
{
    use Dispatchable;
    use HasQueueTags;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries;

    /** @var array<int, int> */
    public array $backoff;

    public int $timeout;

    public bool $failOnTimeout = true;

    public function __construct(
        public readonly string $workspaceId,
    ) {
        $policy = app(RetryPolicyResolver::class)->forQueue($this->queueName());

        $this->tries = $policy->tries;
        $this->backoff = $policy->backoff;
        $this->timeout = $policy->timeout;

        $this->onConnection(config('queues.redis_connection', 'redis'));
        $this->onQueue($this->queueName());
    }

    abstract public function queueName(): string;

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            new ApplyRetryPolicy,
            new LogJobLifecycle,
        ];
    }

    /**
     * @return array<int, string>
     */
    public function tags(): array
    {
        return $this->baseTags();
    }

    public function failed(?Throwable $exception): void
    {
        app(QueueFailedJobHandler::class)->handle($this, $exception);
    }
}
