<?php

declare(strict_types=1);

namespace App\Domains\Analytics\Data;

use App\Domains\Shared\Data\DataTransferObject;
use Carbon\CarbonInterface;

/**
 * @param  array<string, mixed>|null  $hookPerformance
 * @param  array<string, mixed>|null  $contentFeatures
 * @param  array<string, mixed>|null  $mlFeatures
 * @param  array<string, mixed>  $metadata
 */
final class CreatePostPerformanceSnapshotDto extends DataTransferObject
{
    public function __construct(
        public readonly string $workspaceId,
        public readonly string $entityType,
        public readonly string $entityId,
        public readonly CarbonInterface $observedAt,
        public readonly int $impressions = 0,
        public readonly int $likes = 0,
        public readonly int $comments = 0,
        public readonly int $reposts = 0,
        public readonly int $saves = 0,
        public readonly ?string $providerPostId = null,
        public readonly ?CarbonInterface $postedAt = null,
        public readonly ?array $hookPerformance = null,
        public readonly ?array $contentFeatures = null,
        public readonly ?array $mlFeatures = null,
        public readonly array $metadata = [],
        public readonly ?float $engagementRate = null,
        public readonly ?float $normalizedEngagement = null,
    ) {
    }
}
