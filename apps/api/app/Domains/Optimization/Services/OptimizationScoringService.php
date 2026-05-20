<?php

declare(strict_types=1);

namespace App\Domains\Optimization\Services;

use App\Domains\Optimization\Data\CreateOptimizationSnapshotDto;

final class OptimizationScoringService
{
    public function score(
        int $sampleSize,
        ?float $upliftPct,
        float $observedValue = 0.0,
    ): int {
        $sampleBoost = min(40, $sampleSize * 8);
        $upliftBoost = $upliftPct !== null
            ? min(45, abs($upliftPct) * 1.5)
            : min(25, $observedValue * 5);

        return (int) min(100, max(0, round(15 + $sampleBoost + $upliftBoost)));
    }

    public function confidence(int $sampleSize, ?float $upliftPct): float
    {
        $sampleFactor = min(1.0, $sampleSize / 10);
        $upliftFactor = $upliftPct !== null
            ? min(1.0, abs($upliftPct) / 50)
            : 0.3;

        return round(min(0.99, 0.2 + ($sampleFactor * 0.5) + ($upliftFactor * 0.3)), 4);
    }

    public function apply(CreateOptimizationSnapshotDto $draft, int $sampleSize, ?float $upliftPct): CreateOptimizationSnapshotDto
    {
        $score = $this->score($sampleSize, $upliftPct, (float) ($draft->observedMetrics['value'] ?? 0));
        $confidence = $this->confidence($sampleSize, $upliftPct);

        return new CreateOptimizationSnapshotDto(
            workspaceId: $draft->workspaceId,
            optimizationLoopId: $draft->optimizationLoopId,
            cycleNumber: $draft->cycleNumber,
            engine: $draft->engine,
            focus: $draft->focus,
            title: $draft->title,
            summary: $draft->summary,
            rationale: $draft->rationale,
            score: $score,
            confidence: $confidence,
            status: $draft->status,
            baselineMetrics: $draft->baselineMetrics,
            observedMetrics: $draft->observedMetrics,
            deltaMetrics: array_merge($draft->deltaMetrics, [
                'uplift_pct' => $upliftPct,
                'sample_size' => $sampleSize,
            ]),
            evidence: array_merge($draft->evidence, [
                'sample_size' => $sampleSize,
                'uplift_pct' => $upliftPct,
            ]),
            actionPayload: $draft->actionPayload,
            personalizationContext: $draft->personalizationContext,
            mlFeatures: $draft->mlFeatures,
            capturedAt: $draft->capturedAt,
            idempotencyKey: $draft->idempotencyKey,
        );
    }
}
