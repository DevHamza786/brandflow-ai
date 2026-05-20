<?php

declare(strict_types=1);

namespace App\Domains\Autonomous\Services;

use App\Domains\AI\Contracts\GeneratedOutputRepositoryContract;
use App\Domains\AI\Enums\GeneratedOutputStatus;
use App\Domains\AI\Enums\GeneratedOutputType;
use App\Domains\Autonomous\Data\AutonomousContextDto;
use App\Domains\Autonomous\Data\AutonomousWorkflowDto;
use App\Domains\Autonomous\Data\CreateAutonomousExecutionSnapshotDto;
use App\Domains\Autonomous\Enums\AutonomousDecisionType;
use App\Domains\Autonomous\Enums\AutonomousExecutionStatus;
use App\Domains\Autonomous\Enums\AutonomousWorkflowMode;
use App\Domains\Autonomous\Enums\AutonomousWorkflowStatus;
use App\Domains\Recommendations\Enums\RecommendationType;

final class ContentSelectionEngine
{
    public const ENGINE = 'content_selection';

    public function __construct(
        private readonly GeneratedOutputRepositoryContract $generatedOutputs,
        private readonly RecommendationConfidenceEngine $confidence,
    ) {
    }

    public function decide(
        AutonomousContextDto $context,
        AutonomousWorkflowDto $workflow,
        int $cycleNumber,
    ): ?CreateAutonomousExecutionSnapshotDto {
        $hookRec = null;
        foreach ($context->recommendations as $rec) {
            if ($rec->type === RecommendationType::HookStyle) {
                $hookRec = $rec;
                break;
            }
        }

        $paginator = $this->generatedOutputs->paginateForWorkspace(
            $context->workspaceId,
            [
                'type' => GeneratedOutputType::Hook,
                'status' => GeneratedOutputStatus::Completed,
            ],
            10,
        );

        $items = $paginator->items();
        $bestOutput = $items[0] ?? null;
        if ($hookRec === null && $bestOutput === null) {
            return null;
        }

        $variantText = null;
        $outputId = null;
        if ($bestOutput !== null) {
            $outputId = $bestOutput->id;
            $payload = $bestOutput->output?->payload ?? [];
            $variants = is_array($payload['variants'] ?? null) ? $payload['variants'] : [];
            $variantText = is_array($variants[0] ?? null)
                ? (string) ($variants[0]['text'] ?? $variants[0]['hook'] ?? '')
                : null;
        }

        $recConfidence = $hookRec !== null ? $this->confidence->inferConfidence($hookRec) : 0.45;
        $overall = $bestOutput?->scores->overall ?? 70.0;
        $outputConfidence = $bestOutput !== null ? min(0.95, 0.5 + ($overall / 200)) : 0.4;
        $confidence = $this->confidence->combinedConfidence($recConfidence, $outputConfidence);

        [$status, $blocked] = $this->resolveStatus($context, $confidence, $hookRec);

        $style = $hookRec?->actionPayload['style'] ?? 'top_variant';

        return new CreateAutonomousExecutionSnapshotDto(
            workspaceId: $context->workspaceId,
            autonomousWorkflowId: $workflow->id,
            cycleNumber: $cycleNumber,
            status: $status,
            decisionType: AutonomousDecisionType::ContentSelection,
            engine: self::ENGINE,
            focus: 'variant:'.$style,
            title: 'Autonomous content variant',
            summary: $variantText !== null && $variantText !== ''
                ? sprintf('AI-selected hook variant (%.0f%% confidence): “%s”', $confidence * 100, mb_substr($variantText, 0, 80))
                : sprintf('AI-selected hook style “%s” (%.0f%% confidence).', (string) $style, $confidence * 100),
            rationale: 'Combines optimization/recommendation hook signals with top completed generated output.',
            blockedReason: $blocked,
            score: (int) round($confidence * 100),
            confidence: $confidence,
            decisionPayload: [
                'style' => $style,
                'variant_preview' => $variantText,
                'generated_output_id' => $outputId,
            ],
            evidence: [
                'recommendation_id' => $hookRec?->id,
                'output_scores' => $bestOutput !== null ? [
                    'overall' => $bestOutput->scores->overall,
                    'dimensions' => $bestOutput->scores->dimensions,
                ] : null,
            ],
            actionPayload: [
                'action' => 'select_content_variant',
                'style' => $style,
                'publish' => false,
            ],
            personalizationContext: [],
            recommendationId: $hookRec?->id,
            generatedOutputId: $outputId,
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
}
