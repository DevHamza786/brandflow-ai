<?php

declare(strict_types=1);

namespace App\Domains\Schedule\Data;

use App\Domains\Schedule\Enums\SchedulePattern;
use App\Domains\Schedule\Enums\SchedulePlatform;
use App\Domains\Schedule\Enums\ScheduledPostStatus;
use App\Domains\Shared\Data\DataTransferObject;
use Carbon\CarbonInterface;

final class CreateScheduledPostDto extends DataTransferObject
{
    /**
     * @param  array<string, mixed>  $metadata
     * @param  array<string, mixed>|null  $recurrenceRule
     * @param  array<string, mixed>  $orchestrationMetadata  Automation/analytics fingerprints.
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
        public readonly ScheduledPostStatus $status = ScheduledPostStatus::Queued,
        public readonly array $metadata = [],
        public readonly SchedulePlatform $platform = SchedulePlatform::LinkedIn,
        public readonly SchedulePattern $pattern = SchedulePattern::Once,
        public readonly ?array $recurrenceRule = null,
        public readonly ?string $seriesId = null,
        public readonly ?string $workflowRunId = null,
        public readonly array $orchestrationMetadata = [],
    ) {
    }

    /**
     * Bridges {@see SchedulePostCommandDto} payloads into persistence shape.
     */
    public static function fromSchedulePostCommand(SchedulePostCommandDto $cmd, ScheduledPostStatus $status): self
    {
        return new self(
            workspaceId: $cmd->workspaceId,
            linkedinIntegrationId: $cmd->linkedinIntegrationId,
            scheduledFor: $cmd->scheduledFor,
            content: $cmd->content,
            generatedOutputId: $cmd->generatedOutputId,
            contentItemId: $cmd->contentItemId,
            contentVersionId: $cmd->contentVersionId,
            timezone: $cmd->timezone,
            status: $status,
            metadata: array_merge(['source' => $cmd->source], $cmd->metadata),
            platform: $cmd->platform,
            pattern: $cmd->pattern,
            recurrenceRule: $cmd->recurrenceRule,
            seriesId: $cmd->seriesId,
            workflowRunId: $cmd->workflowRunId,
            orchestrationMetadata: $cmd->orchestrationMetadata,
        );
    }
}
