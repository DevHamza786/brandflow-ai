<?php

declare(strict_types=1);

namespace App\Domains\Optimization\Services;

use App\Domains\Analytics\Contracts\PostPerformanceSnapshotRepositoryContract;
use App\Domains\Optimization\Data\CreateOptimizationSnapshotDto;
use App\Domains\Optimization\Data\OptimizationContextDto;
use App\Domains\Optimization\Data\OptimizationLoopDto;
use App\Domains\Optimization\Enums\OptimizationSnapshotStatus;

final class PostingTimeOptimizationEngine
{
    public const ENGINE = 'posting_time';

    public function __construct(
        private readonly PostPerformanceSnapshotRepositoryContract $snapshots,
        private readonly OptimizationScoringService $scoring,
    ) {
    }

    public function analyze(
        OptimizationContextDto $context,
        OptimizationLoopDto $loop,
        int $cycleNumber,
    ): ?CreateOptimizationSnapshotDto {
        $minSamples = (int) config('optimization.min_samples_period', 3);
        $histogram = $this->snapshots->postingHourHistogram($context->workspaceId, $context->lookbackDays);

        if ($histogram === []) {
            return null;
        }

        $sorted = $histogram;
        usort($sorted, static fn ($a, $b) => $b['avg_normalized'] <=> $a['avg_normalized']);
        $best = $sorted[0];
        $worst = $sorted[count($sorted) - 1];

        if ($best['sample_count'] < $minSamples) {
            return null;
        }

        $uplift = $worst['avg_normalized'] > 0
            ? round((($best['avg_normalized'] - $worst['avg_normalized']) / max(0.0001, $worst['avg_normalized'])) * 100, 1)
            : 0.0;

        if ($uplift < (float) config('optimization.min_uplift_pct', 10.0)) {
            return null;
        }

        $hourLabel = sprintf('%02d:00', (int) $best['hour'] % 24);

        $draft = new CreateOptimizationSnapshotDto(
            workspaceId: $context->workspaceId,
            optimizationLoopId: $loop->id,
            cycleNumber: $cycleNumber,
            engine: self::ENGINE,
            focus: 'hour:'.(int) $best['hour'],
            title: 'Best posting window: '.$hourLabel.' UTC',
            summary: sprintf(
                'Posts published around %s UTC averaged %.1f%% higher normalized engagement than your weakest hour in the last %d days.',
                $hourLabel,
                $uplift,
                $context->lookbackDays,
            ),
            rationale: 'Hour-of-day histogram from analytics snapshots — schedule orchestration can consume action_payload.',
            score: 0,
            confidence: null,
            status: OptimizationSnapshotStatus::Proposed,
            baselineMetrics: [
                'worst_hour' => $worst['hour'],
                'avg_normalized' => $worst['avg_normalized'],
            ],
            observedMetrics: [
                'best_hour' => $best['hour'],
                'avg_normalized' => $best['avg_normalized'],
                'sample_count' => $best['sample_count'],
            ],
            deltaMetrics: ['best_hour' => $best['hour'], 'avoid_hour' => $worst['hour']],
            evidence: ['histogram' => array_slice($sorted, 0, 8)],
            actionPayload: [
                'action' => 'schedule_at_hour_utc',
                'hour' => $best['hour'],
                'avoid_hour' => $worst['hour'],
            ],
            personalizationContext: $context->personalizationBase,
            idempotencyKey: $loop->id.':'.$cycleNumber.':'.self::ENGINE.':hour'.$best['hour'],
        );

        return $this->scoring->apply($draft, (int) $best['sample_count'], $uplift);
    }
}
