<?php

declare(strict_types=1);

namespace App\Domains\Schedule\Data;

use App\Domains\Schedule\Enums\SchedulePattern;
use App\Domains\Schedule\Enums\SchedulePlatform;
use App\Domains\Schedule\Enums\ScheduledPostStatus;
use App\Domains\Shared\Data\DataTransferObject;
use Carbon\CarbonInterface;

final class ScheduledPostDto extends DataTransferObject
{
    /**
     * @param  array<string, mixed>|null  $recurrenceRule
     * @param  array<string, mixed>  $metadata
     * @param  array<string, mixed>|null  $errorDetails
     * @param  array<string, mixed>|null  $orchestrationMetadata
     */
    public function __construct(
        public readonly string $id,
        public readonly string $workspaceId,
        public readonly SchedulePlatform $platform,
        public readonly SchedulePattern $pattern,
        public readonly ?array $recurrenceRule,
        public readonly ?string $seriesId,
        public readonly ?string $linkedinIntegrationId,
        public readonly ?string $generatedOutputId,
        public readonly ?string $contentItemId,
        public readonly ?string $contentVersionId,
        public readonly ?string $content,
        public readonly CarbonInterface $publishAt,
        public readonly ?CarbonInterface $scheduledFor,
        public readonly string $timezone,
        public readonly ScheduledPostStatus $status,
        public readonly ?string $workflowRunId,
        public readonly ?string $executionId,
        public readonly ?CarbonInterface $lastDispatchedAt,
        public readonly ?array $orchestrationMetadata,
        public readonly ?string $providerPostId,
        public readonly ?string $linkedinUrn,
        public readonly ?CarbonInterface $publishedAt,
        public readonly ?CarbonInterface $lastAttemptAt,
        public readonly int $attemptCount,
        public readonly ?array $error,
        public readonly ?array $errorDetails,
        public readonly array $metadata,
        public readonly ?CarbonInterface $createdAt = null,
        public readonly ?CarbonInterface $updatedAt = null,
    ) {
    }
}
