<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Data;

use App\Domains\Shared\Data\DataTransferObject;

/**
 * Read model for query layer / workflows / future dashboards.
 *
 * @param  list<array<string, mixed>>  $hookPatternInsights
 * @param  array<string, mixed>  $benchmark
 * @param  array<string, mixed>  $trends
 * @param  list<array<string, mixed>>  $recommendationHints
 */
final class CompetitorIntelligenceReportDto extends DataTransferObject
{
    public function __construct(
        public readonly CompetitorDto $competitor,
        public readonly ?CompetitorSnapshotDto $latestSnapshot,
        public readonly array $hookPatternInsights,
        public readonly array $benchmark,
        public readonly array $trends,
        public readonly ?float $intelligenceScore,
        public readonly array $recommendationHints,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'competitor' => $this->competitor->toArray(),
            'latest_snapshot' => $this->latestSnapshot?->toArray(),
            'hook_pattern_insights' => $this->hookPatternInsights,
            'benchmark' => $this->benchmark,
            'trends' => $this->trends,
            'intelligence_score' => $this->intelligenceScore,
            'recommendation_hints' => $this->recommendationHints,
        ];
    }
}
