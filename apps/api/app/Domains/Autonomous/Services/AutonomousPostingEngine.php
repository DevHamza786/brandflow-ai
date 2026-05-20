<?php

declare(strict_types=1);

namespace App\Domains\Autonomous\Services;

use App\Domains\Autonomous\Contracts\AutonomousExecutionSnapshotRepositoryContract;
use App\Domains\Autonomous\Contracts\AutonomousMlCompatibilityLayerContract;
use App\Domains\Autonomous\Contracts\AutonomousWorkflowRepositoryContract;
use App\Domains\Autonomous\Data\AutonomousContextDto;
use App\Domains\Autonomous\Data\AutonomousWorkflowDto;
use App\Domains\Autonomous\Data\CreateAutonomousExecutionSnapshotDto;
use App\Domains\Autonomous\Data\RunAutonomousExecutionResultDto;
use App\Domains\Autonomous\Enums\AutonomousExecutionStatus;
use App\Domains\Autonomous\Events\AutonomousDecisionRecorded;
use App\Domains\Autonomous\Events\AutonomousExecutionCompleted;
use App\Domains\Autonomous\Events\AutonomousExecutionStarted;
use App\Domains\Recommendations\Contracts\RecommendationRepositoryContract;
use Illuminate\Support\Str;

/**
 * Core autonomous cycle — decide, persist snapshots, never auto-publish in v1.
 */
final class AutonomousPostingEngine
{
    public function __construct(
        private readonly AutonomousWorkflowRepositoryContract $workflows,
        private readonly AutonomousExecutionSnapshotRepositoryContract $snapshots,
        private readonly AutonomousAnalyticsIntegration $analytics,
        private readonly AutonomousOptimizationIntegration $optimization,
        private readonly RecommendationRepositoryContract $recommendations,
        private readonly PostingTimeDecisionEngine $postingTime,
        private readonly ContentSelectionEngine $contentSelection,
        private readonly PostingDecisionEngine $postingDecision,
        private readonly AutonomousMlCompatibilityLayerContract $mlLayer,
        private readonly AutonomousExecutionLogger $logger,
    ) {
    }

    public function runCycle(string $workspaceId): RunAutonomousExecutionResultDto
    {
        $workflow = $this->workflows->findOrCreateDefault($workspaceId);
        $lockToken = (string) Str::uuid();

        if (! $this->workflows->tryAcquireLock($workspaceId, $workflow->id, $lockToken)) {
            throw new \RuntimeException('Autonomous workflow is locked by another execution.');
        }

        try {
            $workflow = $this->workflows->incrementCycle($workspaceId, $workflow->id);
            $cycleNumber = $workflow->currentCycle;

            event(new AutonomousExecutionStarted($workflow, $cycleNumber));

            $context = $this->buildContext($workspaceId, $workflow);

            $timingDraft = $this->postingTime->decide($context, $workflow, $cycleNumber);
            $contentDraft = $this->contentSelection->decide($context, $workflow, $cycleNumber);
            $compositeDraft = $this->postingDecision->decide(
                $context,
                $workflow,
                $cycleNumber,
                $timingDraft,
                $contentDraft,
            );

            $drafts = array_values(array_filter([$timingDraft, $contentDraft, $compositeDraft]));
            $persisted = [];
            $countsByStatus = [];
            $blocked = 0;
            $approved = 0;

            foreach ($drafts as $draft) {
                if ($this->snapshots->existsByIdempotencyKey($draft->idempotencyKey)) {
                    $this->logger->info('duplicate_skipped', ['key' => $draft->idempotencyKey]);
                    continue;
                }

                $enriched = $this->enrich($draft);
                $snapshot = $this->snapshots->create($enriched);
                $persisted[] = $snapshot;
                $countsByStatus[$snapshot->status->value] = ($countsByStatus[$snapshot->status->value] ?? 0) + 1;

                if ($snapshot->status === AutonomousExecutionStatus::BlockedLowConfidence
                    || str_starts_with($snapshot->status->value, 'blocked_')) {
                    $blocked++;
                }
                if ($snapshot->status === AutonomousExecutionStatus::Approved) {
                    $approved++;
                }

                event(new AutonomousDecisionRecorded($snapshot));
            }

            $this->logger->info('cycle_completed', [
                'workspace_id' => $workspaceId,
                'workflow_id' => $workflow->id,
                'cycle' => $cycleNumber,
                'snapshots' => count($persisted),
                'blocked' => $blocked,
            ]);

            event(new AutonomousExecutionCompleted($workflow, $cycleNumber, count($persisted), $blocked));

            return new RunAutonomousExecutionResultDto(
                workflow: $workflow,
                cycleNumber: $cycleNumber,
                snapshotsCreated: count($persisted),
                blockedCount: $blocked,
                approvedCount: $approved,
                snapshots: $persisted,
                countsByStatus: $countsByStatus,
            );
        } finally {
            $this->workflows->releaseLock($workspaceId, $workflow->id, $lockToken);
        }
    }

    private function buildContext(string $workspaceId, AutonomousWorkflowDto $workflow): AutonomousContextDto
    {
        $config = $workflow->config;

        return new AutonomousContextDto(
            workspaceId: $workspaceId,
            workflow: $workflow,
            recommendations: $this->recommendations->listActive(
                $workspaceId,
                minScore: (int) ($config['min_recommendation_score'] ?? config('autonomous.min_recommendation_score', 50)),
            ),
            optimizationSnapshots: $this->optimization->latestSnapshots($workspaceId),
            analyticsSummary: $this->analytics->buildSummary($workspaceId),
            minConfidence: (float) ($config['min_confidence'] ?? config('autonomous.min_confidence', 0.65)),
            minRecommendationScore: (int) ($config['min_recommendation_score'] ?? config('autonomous.min_recommendation_score', 50)),
            thresholds: $config,
        );
    }

    private function enrich(CreateAutonomousExecutionSnapshotDto $draft): CreateAutonomousExecutionSnapshotDto
    {
        return new CreateAutonomousExecutionSnapshotDto(
            workspaceId: $draft->workspaceId,
            autonomousWorkflowId: $draft->autonomousWorkflowId,
            cycleNumber: $draft->cycleNumber,
            status: $draft->status,
            decisionType: $draft->decisionType,
            engine: $draft->engine,
            focus: $draft->focus,
            title: $draft->title,
            summary: $draft->summary,
            rationale: $draft->rationale,
            blockedReason: $draft->blockedReason,
            score: $draft->score,
            confidence: $draft->confidence,
            decisionPayload: $draft->decisionPayload,
            evidence: $draft->evidence,
            actionPayload: $draft->actionPayload,
            personalizationContext: $draft->personalizationContext,
            mlFeatures: $this->mlLayer->enrichFeatures($draft),
            recommendationId: $draft->recommendationId,
            scheduledPostId: $draft->scheduledPostId,
            generatedOutputId: $draft->generatedOutputId,
            capturedAt: $draft->capturedAt,
            idempotencyKey: $draft->idempotencyKey,
        );
    }
}
