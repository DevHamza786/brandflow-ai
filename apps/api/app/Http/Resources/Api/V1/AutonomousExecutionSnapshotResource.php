<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Domains\Autonomous\Data\AutonomousExecutionSnapshotDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property-read AutonomousExecutionSnapshotDto $resource */
final class AutonomousExecutionSnapshotResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $dto = $this->resource;

        return [
            'id' => $dto->id,
            'workspace_id' => $dto->workspaceId,
            'autonomous_workflow_id' => $dto->autonomousWorkflowId,
            'cycle_number' => $dto->cycleNumber,
            'status' => $dto->status->value,
            'decision_type' => $dto->decisionType->value,
            'engine' => $dto->engine,
            'focus' => $dto->focus,
            'score' => $dto->score,
            'confidence' => $dto->confidence,
            'title' => $dto->title,
            'summary' => $dto->summary,
            'rationale' => $dto->rationale,
            'blocked_reason' => $dto->blockedReason,
            'decision_payload' => $dto->decisionPayload,
            'evidence' => $dto->evidence,
            'action_payload' => $dto->actionPayload,
            'personalization_context' => $dto->personalizationContext,
            'ml_features' => $dto->mlFeatures,
            'recommendation_id' => $dto->recommendationId,
            'scheduled_post_id' => $dto->scheduledPostId,
            'generated_output_id' => $dto->generatedOutputId,
            'captured_at' => $dto->capturedAt->toIso8601String(),
        ];
    }
}
