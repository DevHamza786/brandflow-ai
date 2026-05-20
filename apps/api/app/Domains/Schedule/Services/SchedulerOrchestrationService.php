<?php

declare(strict_types=1);

namespace App\Domains\Schedule\Services;

use App\Domains\Schedule\Contracts\ScheduledPostRepositoryContract;
use App\Domains\Schedule\Data\OrchestrationBatchResultDto;
use App\Domains\Schedule\Enums\ScheduleExecutionPhase;
use App\Domains\Schedule\Jobs\PublishLinkedInPostJob;
use App\Domains\Schedule\Pipelines\ScheduleExecutionPipeline;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Cron + queue entrypoint — claims due rows atomically then fans out publish workers.
 *
 * Immediate publishes bypass this path (already `queued` + job from {@see SchedulePostAction}).
 */
final class SchedulerOrchestrationService
{
    public function __construct(
        private readonly ScheduledPostRepositoryContract $scheduledPosts,
        private readonly ScheduleExecutionPipeline $pipeline,
        private readonly ScheduleExecutionLogger $logger,
    ) {
    }

    public function processDuePosts(?string $workspaceId, int $batchLimit, string $traceId): OrchestrationBatchResultDto
    {
        $batchLimit = $batchLimit > 0
            ? $batchLimit
            : (int) config('scheduling.orchestration_batch_limit', 100);

        $logWorkspace = $workspaceId ?? (string) config('scheduling.orchestrator_workspace_id');

        $this->logger->record(
            workspaceId: $logWorkspace,
            scheduledPostId: null,
            phase: ScheduleExecutionPhase::BatchOpened,
            traceId: $traceId,
            payload: [
                'scoped_workspace' => $workspaceId,
                'batch_limit' => $batchLimit,
            ],
        );

        $claimed = [];

        DB::transaction(function () use ($workspaceId, $batchLimit, $traceId, &$claimed): void {
            $claimed = $this->scheduledPosts->claimDueScheduledPosts($workspaceId, $batchLimit);

            foreach ($claimed as $row) {
                $this->logger->record(
                    workspaceId: $row->workspaceId,
                    scheduledPostId: $row->id,
                    phase: ScheduleExecutionPhase::RowClaimed,
                    traceId: $traceId,
                    payload: [
                        'execution_id' => $row->executionId,
                        'pattern' => $row->pattern->value,
                        'platform' => $row->platform->value,
                    ],
                );
            }
        });

        $dispatches = [];
        foreach ($claimed as $row) {
            $dispatches[] = $this->pipeline->dispatchQueuedPublishJob($row, $traceId);
        }

        $this->logger->record(
            workspaceId: $logWorkspace,
            scheduledPostId: null,
            phase: ScheduleExecutionPhase::BatchClosed,
            traceId: $traceId,
            payload: [
                'claimed' => count($dispatches),
            ],
        );

        return new OrchestrationBatchResultDto(
            traceId: $traceId,
            claimedCount: count($dispatches),
            dispatches: $dispatches,
        );
    }

    /**
     * Re-dispatch publish jobs for rows stuck in `queued` (worker crash / lost jobs).
     */
    public function recoverStaleQueued(?string $workspaceId, int $limit, string $traceId): int
    {
        $threshold = now()->subMinutes((int) config('scheduling.stale_queued_ttl_minutes', 45));
        $limit = $limit > 0 ? $limit : (int) config('scheduling.recovery_batch_limit', 25);

        $rows = $this->scheduledPosts->listStaleQueuedForRecovery($workspaceId, $threshold, $limit);

        $count = 0;
        foreach ($rows as $row) {
            PublishLinkedInPostJob::dispatch($row->workspaceId, $row->id);
            $this->logger->record(
                workspaceId: $row->workspaceId,
                scheduledPostId: $row->id,
                phase: ScheduleExecutionPhase::StaleRecoverRedispatch,
                traceId: $traceId,
                payload: ['reason' => 'stale_queued_recovery'],
            );
            $count++;
        }

        return $count;
    }

    public function newTraceId(): string
    {
        return (string) Str::uuid();
    }
}
