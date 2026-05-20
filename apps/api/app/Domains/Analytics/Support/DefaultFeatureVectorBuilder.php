<?php

declare(strict_types=1);

namespace App\Domains\Analytics\Support;

use App\Domains\Analytics\Contracts\FeatureVectorBuilderContract;
use App\Domains\Analytics\Data\PostPerformanceSnapshotDto;

final class DefaultFeatureVectorBuilder implements FeatureVectorBuilderContract
{
    public function buildFromSnapshot(PostPerformanceSnapshotDto $snapshot): array
    {
        return [
            'impressions' => $snapshot->impressions,
            'likes' => $snapshot->likes,
            'comments' => $snapshot->comments,
            'reposts' => $snapshot->reposts,
            'saves' => $snapshot->saves,
            'engagement_rate' => $snapshot->engagementRate ?? 0.0,
            'normalized_engagement' => $snapshot->normalizedEngagement ?? 0.0,
            'hook_overall' => is_array($snapshot->hookPerformance) && isset($snapshot->hookPerformance['overall'])
                ? (float) $snapshot->hookPerformance['overall']
                : 0.0,
        ];
    }
}
