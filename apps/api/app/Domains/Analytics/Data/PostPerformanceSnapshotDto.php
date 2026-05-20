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
final class PostPerformanceSnapshotDto extends DataTransferObject
{
    public function __construct(
        public readonly string $id,
        public readonly string $workspaceId,
        public readonly string $entityType,
        public readonly string $entityId,
        public readonly ?string $providerPostId,
        public readonly ?CarbonInterface $postedAt,
        public readonly CarbonInterface $observedAt,
        public readonly int $impressions,
        public readonly int $likes,
        public readonly int $comments,
        public readonly int $reposts,
        public readonly int $saves,
        public readonly ?float $engagementRate,
        public readonly ?float $normalizedEngagement,
        public readonly ?array $hookPerformance,
        public readonly ?array $contentFeatures,
        public readonly ?array $mlFeatures,
        public readonly array $metadata,
        public readonly ?CarbonInterface $createdAt = null,
    ) {
    }
}
