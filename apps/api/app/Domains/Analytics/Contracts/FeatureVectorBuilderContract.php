<?php

declare(strict_types=1);

namespace App\Domains\Analytics\Contracts;

use App\Domains\Analytics\Data\PostPerformanceSnapshotDto;

/**
 * Future: dense vectors for retrieval / fine-tuning — keep outputs small JSON today.
 */
interface FeatureVectorBuilderContract
{
    /**
     * @return array<string, float|int|string>
     */
    public function buildFromSnapshot(PostPerformanceSnapshotDto $snapshot): array;
}
