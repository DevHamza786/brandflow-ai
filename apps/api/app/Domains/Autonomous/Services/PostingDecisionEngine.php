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

/**
 * Composite go/no-go — aggregates timing + content confidence (does not publish).
 */
final class PostingDecisionEngine
{
    public const ENGINE = 'posting_decision';

    public function __construct(
        private readonly RecommendationConfidenceEngine $confidence,
    ) {
    }

    public function decide(
        AutonomousContextDto $context,
        AutonomousWorkflowDto $workflow,
        int $cycleNumber,
        ?CreateAutonomousExecutionSnapshotDto $timing,
        ?CreateAutonomousExecutionSnapshotDto $content,
    ): ?CreateAutonomousExecutionSnapshotDto {
        if ($timing === null && $content === null) {
            return null;
        }

        $timingConf = $timing?->confidence ?? 0.0;
        $contentConf = $content?->confidence ?? 0.0;
        $confidence = $this->confidence->combinedConfidence($timingConf, $contentConf);

        $lowEngagement = (bool) ($context->analyticsSummary['low_engagement_period'] ?? false);
        $shouldPost = $confidence >= $context->minConfidence && ! $lowEngagement;

        [$status, $blocked] = $this->resolveStatus($context, $confidence, $shouldPost);

        return new CreateAutonomousExecutionSnapshotDto(
            workspaceId: $context->workspaceId,
            autonomousWorkflowId: $workflow->id,
            cycleNumber: $cycleNumber,
            status: $status,
            decisionType: AutonomousDecisionType::PostingDecision,
            engine: self::ENGINE,
            focus: $shouldPost ? 'publish:proposed' : 'publish:hold',
            title: $shouldPost ? 'Autonomous publish decision: proceed' : 'Autonomous publish decision: hold',
            summary: $shouldPost
                ? sprintf('Composite confidence %.0f%% — timing and content signals align for a future autonomous publish.', $confidence * 100)
                : sprintf('Composite confidence %.0f%% or low engagement — holding publish (no reckless automation).', $confidence * 100),
            rationale: 'PostingDecisionEngine aggregates child engines; execution defers to Schedule domain when enabled.',
            blockedReason: $blocked,
            score: (int) round($confidence * 100),
            confidence: $confidence,
            decisionPayload: [
                'should_post' => $shouldPost,
                'timing_snapshot_focus' => $timing?->focus,
                'content_snapshot_focus' => $content?->focus,
                'low_engagement_guard' => $lowEngagement,
            ],
            evidence: [
                'timing_confidence' => $timingConf,
                'content_confidence' => $contentConf,
            ],
            actionPayload: [
                'action' => $shouldPost ? 'ready_to_schedule' : 'hold',
                'publish' => false,
            ],
            personalizationContext: ['mode' => $workflow->mode->value],
            idempotencyKey: $workflow->id.':'.$cycleNumber.':'.self::ENGINE,
        );
    }

    /**
     * @return array{0: AutonomousExecutionStatus, 1: ?string}
     */
    private function resolveStatus(
        AutonomousContextDto $context,
        float $confidence,
        bool $shouldPost,
    ): array {
        if ($context->workflow->status === AutonomousWorkflowStatus::Disabled) {
            return [AutonomousExecutionStatus::BlockedDisabled, 'workflow_disabled'];
        }
        if ($context->workflow->manualOverrideEnabled && $context->workflow->mode === AutonomousWorkflowMode::Observe) {
            return [AutonomousExecutionStatus::BlockedManualOverride, 'observe_mode'];
        }
        if (! $shouldPost || $confidence < $context->minConfidence) {
            return [AutonomousExecutionStatus::BlockedLowConfidence, 'composite_below_threshold'];
        }
        if ($context->workflow->mode !== AutonomousWorkflowMode::Execute) {
            return [AutonomousExecutionStatus::Proposed, null];
        }

        return [
            $context->workflow->autonomousExecutionEnabled
                ? AutonomousExecutionStatus::Approved
                : AutonomousExecutionStatus::Proposed,
            null,
        ];
    }
}
