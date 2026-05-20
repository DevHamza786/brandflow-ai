<?php

declare(strict_types=1);

namespace App\Domains\Recommendations\Services;

use App\Domains\Recommendations\Data\CreateRecommendationDto;
use App\Domains\Recommendations\Data\RecommendationContextDto;
use App\Domains\Recommendations\Data\RecommendationEvidenceDto;
use App\Domains\Recommendations\Enums\RecommendationSource;
use App\Domains\Recommendations\Enums\RecommendationType;

final class PostingTimeRecommender
{
    public function __construct(
        private readonly RecommendationScoringService $scoring,
    ) {
    }

    /**
     * @return list<CreateRecommendationDto>
     */
    public function recommend(RecommendationContextDto $context): array
    {
        $minSamples = (int) config('recommendations.min_samples_posting_hour', 2);
        $histogram = $context->postingHourHistogram;
        if ($histogram === []) {
            return [];
        }

        $sorted = $histogram;
        usort($sorted, static fn ($a, $b) => $b['avg_normalized'] <=> $a['avg_normalized']);
        $best = $sorted[0];
        if ($best['sample_count'] < $minSamples) {
            return [];
        }

        $worst = $sorted[count($sorted) - 1];
        $uplift = $worst['avg_normalized'] > 0
            ? (($best['avg_normalized'] - $worst['avg_normalized']) / max(0.0001, $worst['avg_normalized'])) * 100
            : 0.0;

        $hourLabel = $this->formatHour((int) $best['hour']);

        return [
            $this->scoring->apply(
                new CreateRecommendationDto(
                    workspaceId: $context->workspaceId,
                    type: RecommendationType::PostingTime,
                    source: RecommendationSource::PostingTimeAnalyzer,
                    correlationKey: 'posting_time:best_hour:'.$best['hour'],
                    title: 'Post around '.$hourLabel.' UTC',
                    summary: sprintf(
                        'Hour %s UTC shows highest avg normalized engagement (%.3f) across %d posts in your window.',
                        $hourLabel,
                        $best['avg_normalized'],
                        $best['sample_count'],
                    ),
                    rationale: 'Derived from posted_at timestamps in performance snapshots — schedule orchestration can consume `action_payload`.',
                    score: 0,
                    confidence: null,
                    evidence: [],
                    personalizationContext: array_merge($context->personalizationBase, [
                        'cadence_posts_per_week' => $context->postsPerWeek,
                    ]),
                    actionPayload: [
                        'action' => 'schedule_at_hour_utc',
                        'hour' => $best['hour'],
                        'avoid_hour' => $worst['hour'],
                    ],
                ),
                new RecommendationEvidenceDto(
                    insightKind: 'posting_hour',
                    sampleSize: (int) $best['sample_count'],
                    baselineValue: $worst['avg_normalized'],
                    observedValue: $best['avg_normalized'],
                    upliftPct: round($uplift, 1),
                    metrics: ['best_hour' => $best['hour'], 'worst_hour' => $worst['hour']],
                ),
            ),
        ];
    }

    private function formatHour(int $hour): string
    {
        return sprintf('%02d:00', $hour % 24);
    }
}
