<?php

declare(strict_types=1);

namespace App\Domains\Schedule\Actions;

use App\Domains\Schedule\Contracts\ScheduledPostRepositoryContract;
use App\Domains\Schedule\Data\CreateScheduledPostDto;
use App\Domains\Schedule\Data\SchedulePostCommandDto;
use App\Domains\Schedule\Data\ScheduledPostDto;
use App\Domains\Schedule\Enums\ScheduledPostStatus;
use App\Domains\Schedule\Jobs\PublishLinkedInPostJob;

/**
 * Single scheduling entry point — immediate vs future orchestrated publishes (no duplicate delayed + cron path).
 */
final class SchedulePostAction
{
    public function __construct(
        private readonly ScheduledPostRepositoryContract $scheduledPosts,
    ) {
    }

    public function execute(SchedulePostCommandDto $command): ScheduledPostDto
    {
        $isFuture = $command->scheduledFor->isFuture();
        $status = $isFuture ? ScheduledPostStatus::Scheduled : ScheduledPostStatus::Queued;

        $created = $this->scheduledPosts->create(
            CreateScheduledPostDto::fromSchedulePostCommand($command, $status),
        );

        if (! $isFuture) {
            PublishLinkedInPostJob::dispatch($created->workspaceId, $created->id);
        }

        return $this->scheduledPosts->findById($created->workspaceId, $created->id) ?? $created;
    }
}
