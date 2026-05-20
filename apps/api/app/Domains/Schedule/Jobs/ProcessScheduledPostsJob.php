<?php

declare(strict_types=1);

namespace App\Domains\Schedule\Jobs;

use App\Domains\Schedule\Services\SchedulerOrchestrationService;
use App\Queue\Enums\QueueName;
use App\Queue\Jobs\AbstractQueueJob;

/**
 * Batch orchestration tick — runs inside Horizon on the `orchestration` queue ahead of publish workers.
 */
final class ProcessScheduledPostsJob extends AbstractQueueJob
{
    public function __construct(
        string $orchestratorWorkspaceId,
        public readonly ?string $scopedWorkspaceId,
        public readonly int $batchLimit = 0,
    ) {
        parent::__construct($orchestratorWorkspaceId);
    }

    public function queueName(): string
    {
        return QueueName::Orchestration->value;
    }

    /**
     * @return array<int, string>
     */
    public function tags(): array
    {
        return array_merge(parent::tags(), [
            'orchestration:scheduled_posts',
            $this->scopedWorkspaceId !== null ? 'scoped_workspace:'.$this->scopedWorkspaceId : 'scoped_workspace:all',
        ]);
    }

    public function handle(SchedulerOrchestrationService $orchestration): void
    {
        $traceId = $orchestration->newTraceId();
        $limit = $this->batchLimit > 0
            ? $this->batchLimit
            : (int) config('scheduling.orchestration_batch_limit', 100);

        $orchestration->processDuePosts($this->scopedWorkspaceId, $limit, $traceId);
    }
}
