<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Support;

use App\Domains\Intelligence\Contracts\CompetitorMlCompatibilityLayerContract;

final class DefaultCompetitorMlCompatibilityLayer implements CompetitorMlCompatibilityLayerContract
{
    public function buildFeatures(array $analytics, array $payload): array
    {
        return [
            'schema_version' => 1,
            'embedding_ref' => null,
            'vector_stub' => [
                'posts_count' => $analytics['posts_count'] ?? 0,
                'avg_engagement_rate' => $analytics['avg_engagement_rate'] ?? 0,
                'posts_per_week' => $analytics['posts_per_week'] ?? 0,
                'dominant_hook_style' => $analytics['hook_patterns']['dominant_style'] ?? null,
            ],
            'rag_document_ids' => [],
            'predictive_model_ref' => null,
        ];
    }
}
