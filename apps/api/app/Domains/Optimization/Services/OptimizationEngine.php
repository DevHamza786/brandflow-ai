<?php

declare(strict_types=1);

namespace App\Domains\Optimization\Services;

use App\Domains\Optimization\Contracts\OptimizationLoopRepositoryContract;
use App\Domains\Optimization\Contracts\OptimizationMlCompatibilityLayerContract;
use App\Domains\Optimization\Contracts\OptimizationSnapshotRepositoryContract;
use App\Domains\Optimization\Data\CreateOptimizationSnapshotDto;
use App\Domains\Optimization\Data\RunOptimizationCycleResultDto;
use App\Domains\Optimization\Enums\OptimizationLoopType;
use App\Domains\Optimization\Events\OptimizationCycleCompleted;
use App\Domains\Optimization\Events\OptimizationCycleStarted;

/**
 * Core optimization cycle: analytics context → engines → snapshots → recommendations.
 */
final class OptimizationEngine
{
    public function __construct(
        private readonly OptimizationAnalyticsIntegration $analytics,
        private readonly OptimizationLoopRepositoryContract $loops,
        private readonly OptimizationSnapshotRepositoryContract $snapshots,
        private readonly HookOptimizationEngine $hookEngine,
        private readonly PostingTimeOptimizationEngine $postingTimeEngine,
        private readonly CtaOptimizationEngine $ctaEngine,
        private readonly AudienceFitOptimizationEngine $audienceFitEngine,
        private readonly OptimizationRecommendationBridge $bridge,
        private readonly OptimizationMlCompatibilityLayerContract $mlLayer,
        private readonly OptimizationExecutionLogger $logger,
    ) {
    }

    public function runCycle(
        string $workspaceId,
        ?int $lookbackDays = null,
        ?int $comparisonDays = null,
    ): RunOptimizationCycleResultDto {
        $context = $this->analytics->buildContext($workspaceId, $lookbackDays, $comparisonDays);
        $loop = $this->loops->findOrCreateActive($workspaceId, OptimizationLoopType::Composite);
        $loop = $this->loops->incrementCycle($workspaceId, $loop->id);
        $cycleNumber = $loop->currentCycle;

        event(new OptimizationCycleStarted($loop, $cycleNumber));

        $this->logger->info('cycle_started', [
            'workspace_id' => $workspaceId,
            'loop_id' => $loop->id,
            'cycle_number' => $cycleNumber,
            'current_samples' => count($context->currentPeriodSnapshots),
            'previous_samples' => count($context->previousPeriodSnapshots),
        ]);

        $drafts = array_values(array_filter([
            $this->hookEngine->analyze($context, $loop, $cycleNumber),
            $this->postingTimeEngine->analyze($context, $loop, $cycleNumber),
            $this->ctaEngine->analyze($context, $loop, $cycleNumber),
            $this->audienceFitEngine->analyze($context, $loop, $cycleNumber),
        ]));

        $maxSnapshots = (int) config('optimization.max_snapshots_per_cycle', 20);
        $drafts = array_slice($drafts, 0, $maxSnapshots);

        $persisted = [];
        $countsByEngine = [];
        $recommendationsSynced = 0;
        $minScore = (int) config('optimization.min_score_to_persist', 40);

        foreach ($drafts as $draft) {
            if ($draft->score < $minScore) {
                continue;
            }

            $enriched = $this->enrichDraft($draft);
            $snapshot = $this->snapshots->create($enriched);
            $persisted[] = $snapshot;
            $countsByEngine[$snapshot->engine] = ($countsByEngine[$snapshot->engine] ?? 0) + 1;

            if ($this->bridge->syncSnapshot($snapshot)) {
                $recommendationsSynced++;
            }
        }

        $this->logger->info('cycle_completed', [
            'workspace_id' => $workspaceId,
            'loop_id' => $loop->id,
            'cycle_number' => $cycleNumber,
            'snapshots_created' => count($persisted),
            'recommendations_synced' => $recommendationsSynced,
        ]);

        event(new OptimizationCycleCompleted($loop, $cycleNumber, count($persisted), $recommendationsSynced));

        return new RunOptimizationCycleResultDto(
            loop: $loop,
            cycleNumber: $cycleNumber,
            snapshotsCreated: count($persisted),
            recommendationsSynced: $recommendationsSynced,
            snapshots: $persisted,
            countsByEngine: $countsByEngine,
        );
    }

    private function enrichDraft(CreateOptimizationSnapshotDto $draft): CreateOptimizationSnapshotDto
    {
        return new CreateOptimizationSnapshotDto(
            workspaceId: $draft->workspaceId,
            optimizationLoopId: $draft->optimizationLoopId,
            cycleNumber: $draft->cycleNumber,
            engine: $draft->engine,
            focus: $draft->focus,
            title: $draft->title,
            summary: $draft->summary,
            rationale: $draft->rationale,
            score: $draft->score,
            confidence: $draft->confidence,
            status: $draft->status,
            baselineMetrics: $draft->baselineMetrics,
            observedMetrics: $draft->observedMetrics,
            deltaMetrics: $draft->deltaMetrics,
            evidence: $draft->evidence,
            actionPayload: $draft->actionPayload,
            personalizationContext: $draft->personalizationContext,
            mlFeatures: $this->mlLayer->enrichFeatures($draft),
            capturedAt: $draft->capturedAt,
            idempotencyKey: $draft->idempotencyKey,
        );
    }
}
