<?php

declare(strict_types=1);

namespace App\Domains\Schedule\Contracts;

use App\Domains\Schedule\Data\CreateScheduledPostDto;
use App\Domains\Schedule\Data\ScheduledPostDto;
use App\Domains\Schedule\Enums\ScheduledPostStatus;
use App\Domains\Shared\Contracts\WorkspaceScopedRepositoryContract;

interface ScheduledPostRepositoryContract extends WorkspaceScopedRepositoryContract
{
    public function findById(string $workspaceId, string $id): ?ScheduledPostDto;

    public function create(CreateScheduledPostDto $dto): ScheduledPostDto;

    public function updateStatus(
        string $workspaceId,
        string $id,
        ScheduledPostStatus $status,
        ?array $errorDetails = null,
    ): ScheduledPostDto;

    public function markPublishing(string $workspaceId, string $id): ScheduledPostDto;

    public function markPublished(
        string $workspaceId,
        string $id,
        ?string $providerPostId,
        ?string $linkedinUrn,
        array $metadataPatch = [],
    ): ScheduledPostDto;

    public function markFailed(
        string $workspaceId,
        string $id,
        array $errorDetails,
    ): ScheduledPostDto;

    public function incrementAttempt(string $workspaceId, string $id): ScheduledPostDto;

    /**
     * Rows ready for the dispatcher cron (future scheduling engine).
     *
     * @return list<ScheduledPostDto> Rows with status `scheduled` and `scheduled_for` in the past.
     */
    public function listDueForDispatch(?string $workspaceId, int $limit = 100): array;

    /**
     * Transactional: lock & mark due `scheduled` rows as `queued`, assign execution UUIDs.
     *
     * @return list<ScheduledPostDto>
     */
    public function claimDueScheduledPosts(?string $workspaceId, int $limit): array;

    public function markQueued(string $workspaceId, string $id): ScheduledPostDto;

    /**
     * Stuck `queued` publishes (missed workers) for manual / hourly recovery redispatch.
     *
     * @return list<ScheduledPostDto>
     */
    public function listStaleQueuedForRecovery(?string $workspaceId, \DateTimeInterface $threshold, int $limit): array;

    /**
     * Recent publishes / scheduled rows for activity UI (newest first).
     *
     * @return list<ScheduledPostDto>
     */
    public function listRecentForWorkspace(string $workspaceId, int $limit = 50): array;
}
