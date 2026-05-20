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
final class AutonomousExecutionSnapshotDto extends DataTransferObject
{
    public function __construct(
        public readonly string $id,
        public readonly string $workspaceId,
        public readonly string $autonomousWorkflowId,
        public readonly int $cycleNumber,
        public readonly AutonomousExecutionStatus $status,
        public readonly AutonomousDecisionType $decisionType,
        public readonly string $engine,
        public readonly string $focus,
        public readonly int $score,
        public readonly ?float $confidence,
        public readonly string $title,
        public readonly string $summary,
        public readonly ?string $rationale,
        public readonly ?string $blockedReason,
        public readonly array $decisionPayload,
        public readonly array $evidence,
        public readonly array $actionPayload,
        public readonly array $personalizationContext,
        public readonly array $mlFeatures,
        public readonly ?string $recommendationId,
        public readonly ?string $scheduledPostId,
        public readonly ?string $generatedOutputId,
        public readonly CarbonInterface $capturedAt,
        public readonly string $idempotencyKey,
    ) {
    }
}
