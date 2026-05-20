<?php

declare(strict_types=1);

namespace App\Domains\Optimization\Services;

use App\Domains\Optimization\Data\CreateOptimizationSnapshotDto;
use App\Domains\Optimization\Data\OptimizationContextDto;
use App\Domains\Optimization\Data\OptimizationLoopDto;
use App\Domains\Optimization\Enums\OptimizationSnapshotStatus;
use App\Domains\Optimization\Support\HistoricalComparisonSupport;
use App\Domains\Recommendations\Support\HookStyleClassifier;

final class HookOptimizationEngine
{
    public const ENGINE = 'hook_structure';

    public function __construct(
        private readonly HistoricalComparisonSupport $comparison,
        private readonly HookStyleClassifier $classifier,
        private readonly OptimizationScoringService $scoring,
    ) {
    }

    public function analyze(
        OptimizationContextDto $context,
        OptimizationLoopDto $loop,
        int $cycleNumber,
    ): ?CreateOptimizationSnapshotDto {
        $minSamples = (int) config('optimization.min_samples_period', 3);
        $minUplift = (float) config('optimization.min_uplift_pct', 10.0);

        $rows = $this->comparison->hookStyleComparison(
            $context->currentPeriodSnapshots,
            $context->previousPeriodSnapshots,
            $this->classifier,
        );

        if ($rows === []) {
            return null;
        }

        $best = null;
        foreach ($rows as $row) {
            if ($row['sample_current'] < $minSamples) {
                continue;
            }
            $uplift = $row['uplift_pct'] ?? 0.0;
            if ($uplift < $minUplift) {
                continue;
            }
            $best = $row;
            break;
        }

        if ($best === null) {
            return null;
        }

        $label = $best['label'];
        $uplift = (float) ($best['uplift_pct'] ?? 0);
        $summary = sprintf(
            '%s hooks improved normalized engagement by %.1f%% over the last %d days compared to the prior %d days.',
            ucfirst($label),
            $uplift,
            $context->lookbackDays,
            $context->comparisonDays,
        );

        $draft = new CreateOptimizationSnapshotDto(
            workspaceId: $context->workspaceId,
            optimizationLoopId: $loop->id,
            cycleNumber: $cycleNumber,
            engine: self::ENGINE,
            focus: 'style:'.$best['style'],
            title: 'Winning hook structure: '.$label,
            summary: $summary,
            rationale: 'Period-over-period hook-style correlation from post_performance_snapshots.',
            score: 0,
            confidence: null,
            status: OptimizationSnapshotStatus::Proposed,
            baselineMetrics: [
                'period' => 'previous',
                'avg_normalized' => $best['previous_avg'],
                'sample_size' => $best['sample_previous'],
            ],
            observedMetrics: [
                'period' => 'current',
                'avg_normalized' => $best['current_avg'],
                'sample_size' => $best['sample_current'],
                'style' => $best['style'],
            ],
            deltaMetrics: [
                'style' => $best['style'],
                'label' => $label,
            ],
            evidence: ['style_comparison' => $best, 'all_styles' => array_slice($rows, 0, 5)],
            actionPayload: [
                'action' => 'prefer_hook_style',
                'style' => $best['style'],
                'label' => $label,
            ],
            personalizationContext: $context->personalizationBase,
            idempotencyKey: $loop->id.':'.$cycleNumber.':'.self::ENGINE.':'.$best['style'],
        );

        return $this->scoring->apply(
            $draft,
            (int) $best['sample_current'],
            $uplift,
        );
    }
}
