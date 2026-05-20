<?php

declare(strict_types=1);

namespace App\Domains\Recommendations\Services;

use App\Domains\Analytics\Data\PostPerformanceSnapshotDto;
use App\Domains\Recommendations\Data\CreateRecommendationDto;
use App\Domains\Recommendations\Data\RecommendationContextDto;
use App\Domains\Recommendations\Data\RecommendationEvidenceDto;
use App\Domains\Recommendations\Enums\RecommendationSource;
use App\Domains\Recommendations\Enums\RecommendationType;

final class AudienceFitRecommender
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
        if ($context->brandProfile === null) {
            return [];
        }

        $top = $this->topSnapshots($context->snapshots, 5);
        $bottom = $this->bottomSnapshots($context->snapshots, 5);
        if (count($top) < 2 || count($bottom) < 2) {
            return [];
        }

        $topDims = $this->avgDimensions($top);
        $bottomDims = $this->avgDimensions($bottom);
        $gaps = [];
        foreach (['curiosity_gap', 'specificity', 'clarity', 'audience_fit'] as $dim) {
            $delta = ($topDims[$dim] ?? 0) - ($bottomDims[$dim] ?? 0);
            if ($delta >= 8) {
                $gaps[$dim] = round($delta, 1);
            }
        }

        if ($gaps === []) {
            return [];
        }

        $primary = array_key_first($gaps);
        $segments = $context->brandProfile->targetAudience->segments;
        $segmentLabel = $segments !== [] ? $segments[0] : 'your ICP';

        return [
            $this->scoring->apply(
                new CreateRecommendationDto(
                    workspaceId: $context->workspaceId,
                    type: RecommendationType::AudienceFit,
                    source: RecommendationSource::AudienceFitEngine,
                    correlationKey: 'audience_fit:dimension:'.$primary,
                    title: 'Strengthen '.str_replace('_', ' ', $primary).' for '.$segmentLabel,
                    summary: sprintf(
                        'Top performers score %.1f pts higher on %s than bottom-quartile hooks — align variants to %s pain points.',
                        $gaps[$primary],
                        str_replace('_', ' ', $primary),
                        $segmentLabel,
                    ),
                    rationale: 'Compares Hook Lab dimension averages on high vs low normalized posts for this workspace.',
                    score: 0,
                    confidence: null,
                    evidence: ['dimension_gaps' => $gaps],
                    personalizationContext: array_merge($context->personalizationBase, [
                        'primary_segment' => $segmentLabel,
                        'tone' => $context->brandProfile->toneProfile->primary,
                    ]),
                    actionPayload: [
                        'action' => 'boost_dimension',
                        'dimension' => $primary,
                        'target_delta' => $gaps[$primary],
                    ],
                ),
                new RecommendationEvidenceDto(
                    insightKind: 'audience_dimension_gap',
                    sampleSize: count($top) + count($bottom),
                    baselineValue: $bottomDims[$primary] ?? null,
                    observedValue: $topDims[$primary] ?? null,
                    upliftPct: $gaps[$primary],
                    metrics: $gaps,
                ),
            ),
        ];
    }

    /**
     * @param  list<PostPerformanceSnapshotDto>  $snapshots
     * @return list<PostPerformanceSnapshotDto>
     */
    private function topSnapshots(array $snapshots, int $limit): array
    {
        $sorted = $snapshots;
        usort($sorted, static fn ($a, $b) => ($b->normalizedEngagement ?? 0) <=> ($a->normalizedEngagement ?? 0));

        return array_slice($sorted, 0, $limit);
    }

    /**
     * @param  list<PostPerformanceSnapshotDto>  $snapshots
     * @return list<PostPerformanceSnapshotDto>
     */
    private function bottomSnapshots(array $snapshots, int $limit): array
    {
        $sorted = $snapshots;
        usort($sorted, static fn ($a, $b) => ($a->normalizedEngagement ?? 0) <=> ($b->normalizedEngagement ?? 0));

        return array_slice($sorted, 0, $limit);
    }

    /**
     * @param  list<PostPerformanceSnapshotDto>  $snapshots
     * @return array<string, float>
     */
    private function avgDimensions(array $snapshots): array
    {
        $sums = ['curiosity_gap' => 0.0, 'specificity' => 0.0, 'clarity' => 0.0, 'audience_fit' => 0.0];
        $n = 0;
        foreach ($snapshots as $s) {
            $dims = is_array($s->hookPerformance) && isset($s->hookPerformance['dimensions'])
                && is_array($s->hookPerformance['dimensions'])
                ? $s->hookPerformance['dimensions']
                : null;
            if ($dims === null) {
                continue;
            }
            $n++;
            foreach ($sums as $key => $_) {
                $sums[$key] += (float) ($dims[$key] ?? $dims[str_replace('_', '', $key)] ?? 0);
            }
        }
        if ($n === 0) {
            return $sums;
        }
        foreach ($sums as $k => $v) {
            $sums[$k] = $v / $n;
        }

        return $sums;
    }
}
