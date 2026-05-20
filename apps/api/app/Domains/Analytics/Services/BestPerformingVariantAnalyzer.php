<?php

declare(strict_types=1);

namespace App\Domains\Analytics\Services;

use App\Domains\Analytics\Contracts\PostPerformanceSnapshotRepositoryContract;
use App\Domains\Analytics\Data\PostPerformanceSnapshotDto;

/**
 * Best-performing hook / variant selection from snapshots (no dashboard).
 */
final class BestPerformingVariantAnalyzer
{
    public function __construct(
        private readonly PostPerformanceSnapshotRepositoryContract $snapshots,
    ) {
    }

    /**
     * @return list<array{id:string,entity_id:string,normalized:?float,hook_score:?float,hook_text:?string}>
     */
    public function topHooks(string $workspaceId, int $limit = 10): array
    {
        $rows = $this->snapshots->topByNormalizedEngagement($workspaceId, $limit);

        return array_map(fn (PostPerformanceSnapshotDto $s) => [
            'id' => $s->id,
            'entity_id' => $s->entityId,
            'normalized' => $s->normalizedEngagement,
            'hook_score' => is_array($s->hookPerformance) && isset($s->hookPerformance['engine_score'])
                ? (float) $s->hookPerformance['engine_score']
                : null,
            'hook_text' => is_array($s->hookPerformance) && isset($s->hookPerformance['text']) && is_string($s->hookPerformance['text'])
                ? $s->hookPerformance['text']
                : null,
        ], $rows);
    }
}
