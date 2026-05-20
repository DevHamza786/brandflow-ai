<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Data;

use App\Domains\Shared\Data\DataTransferObject;
use Carbon\CarbonInterface;

/**
 * @param  array<string, mixed>  $payload
 * @param  array<string, mixed>  $metadata
 * @param  array<string, mixed>  $engagementMetrics
 * @param  array<string, mixed>  $hookPatterns
 * @param  array<string, mixed>  $postingCadence
 * @param  array<string, mixed>  $contentStructure
 * @param  array<string, mixed>  $ctaPatterns
 * @param  array<string, mixed>  $trendSummary
 * @param  array<string, mixed>  $mlFeatures
 */
final class CompetitorSnapshotDto extends DataTransferObject
{
    public function __construct(
        public readonly string $id,
        public readonly string $workspaceId,
        public readonly string $competitorId,
        public readonly CarbonInterface $capturedAt,
        public readonly array $payload,
        public readonly ?string $contentHash,
        public readonly array $metadata,
        public readonly int $postsCount,
        public readonly ?float $avgEngagementRate,
        public readonly ?float $postsPerWeek,
        public readonly ?float $intelligenceScore,
        public readonly array $engagementMetrics,
        public readonly array $hookPatterns,
        public readonly array $postingCadence,
        public readonly array $contentStructure,
        public readonly array $ctaPatterns,
        public readonly array $trendSummary,
        public readonly array $mlFeatures,
        public readonly ?CarbonInterface $createdAt = null,
    ) {
    }
}
