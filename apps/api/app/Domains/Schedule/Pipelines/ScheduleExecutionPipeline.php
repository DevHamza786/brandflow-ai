<?php

declare(strict_types=1);

namespace App\Domains\Schedule\Pipelines;

use App\Domains\Schedule\Data\DispatchScheduledPostResultDto;
use App\Domains\Schedule\Data\ScheduledPostDto;
use App\Domains\Schedule\Enums\ScheduleExecutionPhase;
use App\Domains\Schedule\Enums\ScheduledPostStatus;
use App\Domains\Schedule\Jobs\PublishLinkedInPostJob;
use App\Domains\Schedule\Services\ScheduleExecutionLogger;

/**
 * Queue-safe steps after DB claim commits: fan out publish workers + audit phases.
 */
final class ScheduleExecutionPipeline
{
    public function __construct(
        private readonly ScheduleExecutionLogger $logger,
    ) {
    }

    public function dispatchQueuedPublishJob(ScheduledPostDto $post, string $batchTraceId): DispatchScheduledPostResultDto
    {
        if ($post->status !== ScheduledPostStatus::Queued) {
            return new DispatchScheduledPostResultDto(
                scheduledPostId: $post->id,
                workspaceId: $post->workspaceId,
                dispatched: false,
                executionId: $post->executionId,
                skippedReason: 'row_not_queued_after_claim',
            );
        }

        PublishLinkedInPostJob::dispatch($post->workspaceId, $post->id);

        $this->logger->record(
            workspaceId: $post->workspaceId,
            scheduledPostId: $post->id,
            phase: ScheduleExecutionPhase::DispatchQueuedJob,
            traceId: $batchTraceId,
            payload: [
                'execution_id' => $post->executionId,
            ],
        );

        return new DispatchScheduledPostResultDto(
            scheduledPostId: $post->id,
            workspaceId: $post->workspaceId,
            dispatched: true,
            executionId: $post->executionId,
            skippedReason: null,
        );
    }
}
