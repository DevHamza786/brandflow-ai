<?php

declare(strict_types=1);

namespace App\Domains\Schedule\Actions;

use App\Domains\Schedule\Data\CreateScheduledPostDto;
use App\Domains\Schedule\Data\ScheduledPostDto;
use App\Domains\Schedule\Data\SchedulePostCommandDto;

/**
 * Thin bridge for HTTP + legacy call sites — delegates to {@see SchedulePostAction}.
 */
final class PublishToLinkedInAction
{
    public function __construct(
        private readonly SchedulePostAction $schedulePost,
    ) {
    }

    public function execute(CreateScheduledPostDto $dto): ScheduledPostDto
    {
        return $this->schedulePost->execute(SchedulePostCommandDto::fromLegacyCreate($dto));
    }
}
