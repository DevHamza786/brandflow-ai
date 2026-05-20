<?php

declare(strict_types=1);

namespace App\Domains\Recommendations\Services;

use App\Domains\Recommendations\Data\CreateRecommendationDto;

/**
 * Dedupes and ranks drafts before persistence.
 */
final class RecommendationAggregationService
{
    /**
     * @param  list<CreateRecommendationDto>  $drafts
     * @return list<CreateRecommendationDto>
     */
    public function aggregate(array $drafts): array
    {
        $byKey = [];
        foreach ($drafts as $draft) {
            $key = $draft->correlationKey;
            if (! isset($byKey[$key]) || $draft->score > $byKey[$key]->score) {
                $byKey[$key] = $draft;
            }
        }

        $merged = array_values($byKey);
        usort($merged, static fn (CreateRecommendationDto $a, CreateRecommendationDto $b) => $b->score <=> $a->score);

        $max = (int) config('recommendations.max_persisted', 50);

        return array_slice($merged, 0, $max);
    }
}
