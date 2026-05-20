<?php

declare(strict_types=1);

namespace App\Domains\Optimization\Services;

use App\Domains\Analytics\Data\PostPerformanceSnapshotDto;
use App\Domains\Optimization\Data\CreateOptimizationSnapshotDto;
use App\Domains\Optimization\Data\OptimizationContextDto;
use App\Domains\Optimization\Data\OptimizationLoopDto;
use App\Domains\Optimization\Enums\OptimizationSnapshotStatus;

final class AudienceFitOptimizationEngine
{
    public const ENGINE = 'audience_fit';

    public function __construct(
        private readonly OptimizationScoringService $scoring,
    ) {
    }

    public function analyze(
        OptimizationContextDto $context,
        OptimizationLoopDto $loop,
        int $cycleNumber,
    ): ?CreateOptimizationSnapshotDto {
        if ($context->brandProfile === null) {
            return null;
        }

        $top = $this->topSnapshots($context->currentPeriodSnapshots, 5);
        $bottom = $this->bottomSnapshots($context->currentPeriodSnapshots, 5);
        if (count($top) < 2 || count($bottom) < 2) {
            return null;
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
            return null;
        }

        $primary = array_key_first($gaps);
        $uplift = $gaps[$primary];
        $segments = $context->brandProfile->targetAudience->segments;
        $segmentLabel = $segments !== [] ? $segments[0] : 'your ICP';

        $draft = new CreateOptimizationSnapshotDto(
            workspaceId: $context->workspaceId,
            optimizationLoopId: $loop->id,
            cycleNumber: $cycleNumber,
            engine: self::ENGINE,
            focus: 'dimension:'.$primary,
            title: 'Audience-fit lift: '.str_replace('_', ' ', $primary),
            summary: sprintf(
                'Top performers score %.1f pts higher on %s than bottom-quartile hooks for %s in the last %d days.',
                $uplift,
                str_replace('_', ' ', $primary),
                $segmentLabel,
                $context->lookbackDays,
            ),
            rationale: 'Compares Hook Lab dimension averages on high vs low normalized posts.',
            score: 0,
            confidence: null,
            status: OptimizationSnapshotStatus::Proposed,
            baselineMetrics: ['bottom_avg' => $bottomDims[$primary] ?? 0],
            observedMetrics: ['top_avg' => $topDims[$primary] ?? 0],
            deltaMetrics: ['dimension_gaps' => $gaps],
            evidence: ['dimension_gaps' => $gaps],
            actionPayload: [
                'action' => 'boost_dimension',
                'dimension' => $primary,
                'target_delta' => $uplift,
            ],
            personalizationContext: array_merge($context->personalizationBase, [
                'primary_segment' => $segmentLabel,
            ]),
            idempotencyKey: $loop->id.':'.$cycleNumber.':'.self::ENGINE.':'.$primary,
        );

        return $this->scoring->apply($draft, count($top) + count($bottom), $uplift);
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
                $sums[$key] += (float) ($dims[$key] ?? 0);
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
