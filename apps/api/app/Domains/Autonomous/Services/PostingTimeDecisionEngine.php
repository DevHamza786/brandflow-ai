<?php

declare(strict_types=1);

namespace App\Domains\Autonomous\Services;

use App\Domains\Autonomous\Data\AutonomousContextDto;
use App\Domains\Autonomous\Data\AutonomousWorkflowDto;
use App\Domains\Autonomous\Data\CreateAutonomousExecutionSnapshotDto;
use App\Domains\Autonomous\Enums\AutonomousDecisionType;
use App\Domains\Autonomous\Enums\AutonomousExecutionStatus;
use App\Domains\Autonomous\Enums\AutonomousWorkflowMode;
use App\Domains\Autonomous\Enums\AutonomousWorkflowStatus;
use App\Domains\Recommendations\Enums\RecommendationType;

final class PostingTimeDecisionEngine
{
    public const ENGINE = 'posting_time_decision';

    public function __construct(
        private readonly RecommendationConfidenceEngine $confidence,
    ) {
    }

    public function decide(
        AutonomousContextDto $context,
        AutonomousWorkflowDto $workflow,
        int $cycleNumber,
    ): ?CreateAutonomousExecutionSnapshotDto {
        $analytics = $context->analyticsSummary;
        $bestHour = $analytics['best_posting_hour'] ?? null;

        $postingRec = null;
        foreach ($context->recommendations as $rec) {
            if ($rec->type === RecommendationType::PostingTime) {
                $postingRec = $rec;
                break;
            }
        }

        $optSnap = null;
        foreach ($context->optimizationSnapshots as $snap) {
            if ($snap->engine === 'posting_time') {
                $optSnap = $snap;
                break;
            }
        }

        if ($bestHour === null && $postingRec === null && $optSnap === null) {
            return null;
        }

        $hour = $bestHour['hour'] ?? ($postingRec?->actionPayload['hour'] ?? $optSnap?->observedMetrics['best_hour'] ?? null);
        if ($hour === null) {
            return null;
        }

        $recConfidence = $postingRec !== null
            ? $this->confidence->inferConfidence($postingRec)
            : 0.5;
        $optConfidence = $optSnap?->confidence ?? 0.5;
        $confidence = $this->confidence->combinedConfidence($recConfidence, (float) $optConfidence);

        [$status, $blocked] = $this->resolveStatus($context, $confidence, $postingRec);

        return new CreateAutonomousExecutionSnapshotDto(
            workspaceId: $context->workspaceId,
            autonomousWorkflowId: $workflow->id,
            cycleNumber: $cycleNumber,
            status: $status,
            decisionType: AutonomousDecisionType::PostingTime,
            engine: self::ENGINE,
            focus: 'hour:'.(int) $hour,
            title: 'Autonomous posting window',
            summary: sprintf(
                'AI-selected publish window: %02d:00 UTC (confidence %.0f%%).',
                (int) $hour % 24,
                $confidence * 100,
            ),
            rationale: 'Derived from analytics histogram, optimization snapshots, and posting-time recommendations.',
            blockedReason: $blocked,
            score: (int) round($confidence * 100),
            confidence: $confidence,
            decisionPayload: [
                'hour_utc' => (int) $hour,
                'would_schedule_at' => $this->nextOccurrenceAtHour((int) $hour)->toIso8601String(),
            ],
            evidence: [
                'analytics' => $bestHour,
                'optimization_snapshot_id' => $optSnap?->id,
            ],
            actionPayload: [
                'action' => 'schedule_at_hour_utc',
                'hour' => (int) $hour,
                'publish' => false,
            ],
            personalizationContext: ['low_engagement' => $analytics['low_engagement_period'] ?? false],
            recommendationId: $postingRec?->id,
            idempotencyKey: $workflow->id.':'.$cycleNumber.':'.self::ENGINE,
        );
    }

    /**
     * @return array{0: AutonomousExecutionStatus, 1: ?string}
     */
    private function resolveStatus(
        AutonomousContextDto $context,
        float $confidence,
        ?\App\Domains\Recommendations\Data\RecommendationDto $rec,
    ): array {
        if ($context->workflow->status === AutonomousWorkflowStatus::Disabled) {
            return [AutonomousExecutionStatus::BlockedDisabled, 'workflow_disabled'];
        }
        if ($context->workflow->manualOverrideEnabled && $context->workflow->mode === AutonomousWorkflowMode::Observe) {
            return [AutonomousExecutionStatus::BlockedManualOverride, 'observe_mode'];
        }
        if ($confidence < $context->minConfidence) {
            return [AutonomousExecutionStatus::BlockedLowConfidence, 'below_min_confidence'];
        }
        if ($rec !== null && ! $this->confidence->passes($rec, $context->minConfidence, $context->minRecommendationScore)) {
            return [AutonomousExecutionStatus::BlockedLowConfidence, 'recommendation_below_threshold'];
        }

        return [
            $context->workflow->autonomousExecutionEnabled
                ? AutonomousExecutionStatus::Approved
                : AutonomousExecutionStatus::Proposed,
            null,
        ];
    }

    private function nextOccurrenceAtHour(int $hour): \Carbon\CarbonInterface
    {
        $next = now()->utc()->setMinute(0)->setSecond(0);
        if ((int) $next->format('G') >= $hour) {
            $next = $next->addDay();
        }

        return $next->setHour($hour);
    }
}
