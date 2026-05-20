<?php

declare(strict_types=1);

namespace App\Domains\Schedule\Data;

use App\Domains\Schedule\Enums\SchedulePattern;
use App\Domains\Schedule\Enums\SchedulePlatform;
use App\Domains\Shared\Data\DataTransferObject;
use Carbon\CarbonInterface;

/**
 * Command envelope for enqueueing publishes (immediate vs orchestrator-managed future publishes).
 *
 * Immediate (`scheduled_for` not in the future) → status `queued`, `PublishLinkedInPostJob` now.
 * Future → status `scheduled`, cron/process job claims without duplicating Redis delayed publishes.
 *
 * Timezone-aware display & calendar overlays stay client-side later; timestamps remain UTC-aware in DB.
 */
final class SchedulePostCommandDto extends DataTransferObject
{
    /**
     * @param  array<string, mixed>  $metadata
     * @param  array<string, mixed>|null  $recurrenceRule  RRULE-compatible JSON foundation
     * @param  array<string, mixed>  $orchestrationMetadata  Automation fingerprints (campaign id, playbook, etc.).
     */
    public function __construct(
        public readonly string $workspaceId,
        public readonly string $linkedinIntegrationId,
        public readonly CarbonInterface $scheduledFor,
        public readonly ?string $content = null,
        public readonly ?string $generatedOutputId = null,
        public readonly ?string $contentItemId = null,
        public readonly ?string $contentVersionId = null,
        public readonly string $timezone = 'UTC',
        public readonly SchedulePlatform $platform = SchedulePlatform::LinkedIn,
        public readonly SchedulePattern $pattern = SchedulePattern::Once,
        public readonly ?array $recurrenceRule = null,
        public readonly ?string $seriesId = null,
        public readonly ?string $workflowRunId = null,
        public readonly array $metadata = [],
        public readonly array $orchestrationMetadata = [],
        public readonly string $source = 'api',
    ) {
    }

    public static function fromLegacyCreate(CreateScheduledPostDto $dto): self
    {
        return new self(
            workspaceId: $dto->workspaceId,
            linkedinIntegrationId: $dto->linkedinIntegrationId,
            scheduledFor: $dto->scheduledFor,
            content: $dto->content,
            generatedOutputId: $dto->generatedOutputId,
            contentItemId: $dto->contentItemId,
            contentVersionId: $dto->contentVersionId,
            timezone: $dto->timezone,
            platform: $dto->platform,
            pattern: $dto->pattern,
            recurrenceRule: $dto->recurrenceRule,
            seriesId: $dto->seriesId,
            workflowRunId: $dto->workflowRunId,
            metadata: array_merge($dto->metadata, [
                'dispatch_mode' => 'legacy_create_dto_bridge',
            ]),
            orchestrationMetadata: $dto->orchestrationMetadata,
            source: (string) ($dto->metadata['source'] ?? 'api'),
        );
    }
}
