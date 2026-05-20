<?php

declare(strict_types=1);

namespace App\Domains\Recommendations\Data;

use App\Domains\Shared\Data\DataTransferObject;

/**
 * Structured evidence for non-generic recommendations (correlation moat).
 *
 * @param  array<string, mixed>  $metrics
 * @param  list<string>  $sample_entity_ids
 */
final class RecommendationEvidenceDto extends DataTransferObject
{
    public function __construct(
        public readonly string $insightKind,
        public readonly int $sampleSize,
        public readonly ?float $baselineValue,
        public readonly ?float $observedValue,
        public readonly ?float $upliftPct,
        public readonly array $metrics = [],
        public readonly array $sampleEntityIds = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toEvidencePayload(): array
    {
        return [
            'insight_kind' => $this->insightKind,
            'sample_size' => $this->sampleSize,
            'baseline_value' => $this->baselineValue,
            'observed_value' => $this->observedValue,
            'uplift_pct' => $this->upliftPct,
            'metrics' => $this->metrics,
            'sample_entity_ids' => $this->sampleEntityIds,
        ];
    }
}
