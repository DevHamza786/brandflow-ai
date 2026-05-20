<?php

declare(strict_types=1);

namespace App\Domains\Autonomous\Data;

use App\Domains\Autonomous\Enums\AutonomousDecisionType;
use App\Domains\Autonomous\Enums\AutonomousExecutionStatus;
use App\Domains\Shared\Data\DataTransferObject;
use Carbon\CarbonInterface;

/**
 * @param  array<string, mixed>  $decisionPayload
 * @param  array<string, mixed>  $evidence
 * @param  array<string, mixed>  $actionPayload
 * @param  array<string, mixed>  $personalizationContext
 * @param  array<string, mixed>  $mlFeatures
 */
final class CreateAutonomousExecutionSnapshotDto extends DataTransferObject
{
    public function __construct(
        public readonly string $workspaceId,
        public readonly string $autonomousWorkflowId,
        public readonly int $cycleNumber,
        public readonly AutonomousExecutionStatus $status,
        public readonly AutonomousDecisionType $decisionType,
        public readonly string $engine,
        public readonly string $focus,
        public readonly string $title,
        public readonly string $summary,
        public readonly ?string $rationale,
        public readonly ?string $blockedReason,
        public readonly int $score,
        public readonly ?float $confidence,
        public readonly array $decisionPayload,
        public readonly array $evidence,
        public readonly array $actionPayload,
        public readonly array $personalizationContext,
        public readonly array $mlFeatures = [],
        public readonly ?string $recommendationId = null,
        public readonly ?string $scheduledPostId = null,
        public readonly ?string $generatedOutputId = null,
        public readonly ?CarbonInterface $capturedAt = null,
        public readonly string $idempotencyKey = '',
    ) {
    }
}
