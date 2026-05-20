<?php

declare(strict_types=1);

namespace App\Domains\Optimization\Services;

use App\Domains\Optimization\Data\OptimizationSnapshotDto;
use App\Domains\Recommendations\Contracts\RecommendationRepositoryContract;
use App\Domains\Recommendations\Data\CreateRecommendationDto;
use App\Domains\Recommendations\Data\RecommendationEvidenceDto;
use App\Domains\Recommendations\Enums\RecommendationSource;
use App\Domains\Recommendations\Enums\RecommendationType;
use App\Domains\Recommendations\Services\RecommendationScoringService;

/**
 * Pushes optimization snapshots into the recommendations store for unified consumption.
 */
final class OptimizationRecommendationBridge
{
    public function __construct(
        private readonly RecommendationRepositoryContract $recommendations,
        private readonly RecommendationScoringService $scoring,
    ) {
    }

    public function syncSnapshot(OptimizationSnapshotDto $snapshot): bool
    {
        $minScore = (int) config('optimization.min_score_to_persist', 40);
        if ($snapshot->score < $minScore) {
            return false;
        }

        $type = $this->mapType($snapshot->engine);
        $key = 'optimization:'.$snapshot->optimizationLoopId.':'.$snapshot->focus;

        $draft = new CreateRecommendationDto(
            workspaceId: $snapshot->workspaceId,
            type: $type,
            source: RecommendationSource::OptimizationLoop,
            correlationKey: $key,
            title: $snapshot->title,
            summary: $snapshot->summary,
            rationale: $snapshot->rationale ?? 'Optimization loop cycle intelligence.',
            score: 0,
            confidence: null,
            evidence: $snapshot->evidence,
            personalizationContext: array_merge($snapshot->personalizationContext, [
                'optimization_loop_id' => $snapshot->optimizationLoopId,
                'optimization_snapshot_id' => $snapshot->id,
                'cycle_number' => $snapshot->cycleNumber,
                'engine' => $snapshot->engine,
            ]),
            actionPayload: $snapshot->actionPayload,
        );

        $uplift = isset($snapshot->deltaMetrics['uplift_pct'])
            ? (float) $snapshot->deltaMetrics['uplift_pct']
            : null;
        $sample = (int) ($snapshot->evidence['sample_size'] ?? $snapshot->observedMetrics['sample_size'] ?? 0);

        $scored = $this->scoring->apply(
            $draft,
            new RecommendationEvidenceDto(
                insightKind: 'optimization_'.$snapshot->engine,
                sampleSize: $sample,
                baselineValue: isset($snapshot->baselineMetrics['avg_normalized'])
                    ? (float) $snapshot->baselineMetrics['avg_normalized']
                    : null,
                observedValue: isset($snapshot->observedMetrics['avg_normalized'])
                    ? (float) $snapshot->observedMetrics['avg_normalized']
                    : null,
                upliftPct: $uplift,
                metrics: [
                    'engine' => $snapshot->engine,
                    'focus' => $snapshot->focus,
                    'cycle_number' => $snapshot->cycleNumber,
                ],
            ),
        );

        if ($scored->score < (int) config('recommendations.min_score_to_persist', 35)) {
            return false;
        }

        $this->recommendations->supersedeActiveByCorrelationKey($snapshot->workspaceId, $key);
        $this->recommendations->create($scored);

        return true;
    }

    private function mapType(string $engine): RecommendationType
    {
        return match ($engine) {
            HookOptimizationEngine::ENGINE => RecommendationType::HookStyle,
            PostingTimeOptimizationEngine::ENGINE => RecommendationType::PostingTime,
            CtaOptimizationEngine::ENGINE => RecommendationType::CtaOptimization,
            AudienceFitOptimizationEngine::ENGINE => RecommendationType::AudienceFit,
            default => RecommendationType::EngagementImprovement,
        };
    }
}
