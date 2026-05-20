<?php

declare(strict_types=1);

namespace App\Domains\Autonomous\Repositories;

use App\Domains\Autonomous\Contracts\AutonomousExecutionSnapshotRepositoryContract;
use App\Domains\Autonomous\Data\AutonomousExecutionSnapshotDto;
use App\Domains\Autonomous\Data\CreateAutonomousExecutionSnapshotDto;
use App\Domains\Autonomous\Enums\AutonomousDecisionType;
use App\Domains\Autonomous\Enums\AutonomousExecutionStatus;
use App\Domains\Autonomous\Models\AutonomousExecutionSnapshot;
use Illuminate\Support\Str;

final class AutonomousExecutionSnapshotRepository implements AutonomousExecutionSnapshotRepositoryContract
{
    public function create(CreateAutonomousExecutionSnapshotDto $dto): AutonomousExecutionSnapshotDto
    {
        $model = AutonomousExecutionSnapshot::query()->create([
            'id' => (string) Str::uuid(),
            'workspace_id' => $dto->workspaceId,
            'autonomous_workflow_id' => $dto->autonomousWorkflowId,
            'cycle_number' => $dto->cycleNumber,
            'status' => $dto->status->value,
            'decision_type' => $dto->decisionType->value,
            'engine' => $dto->engine,
            'focus' => $dto->focus,
            'score' => max(0, min(100, $dto->score)),
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
            'captured_at' => $dto->capturedAt ?? now(),
            'idempotency_key' => $dto->idempotencyKey,
        ]);

        return $this->toDto($model);
    }

    public function existsByIdempotencyKey(string $idempotencyKey): bool
    {
        return AutonomousExecutionSnapshot::query()
            ->where('idempotency_key', $idempotencyKey)
            ->exists();
    }

    public function findById(string $workspaceId, string $id): ?AutonomousExecutionSnapshotDto
    {
        $model = AutonomousExecutionSnapshot::query()
            ->where('workspace_id', $workspaceId)
            ->where('id', $id)
            ->first();

        return $model ? $this->toDto($model) : null;
    }

    public function listByWorkflow(string $workspaceId, string $workflowId, int $limit = 100): array
    {
        $limit = max(1, min($limit, 200));

        return AutonomousExecutionSnapshot::query()
            ->where('workspace_id', $workspaceId)
            ->where('autonomous_workflow_id', $workflowId)
            ->orderByDesc('cycle_number')
            ->orderByDesc('captured_at')
            ->limit($limit)
            ->get()
            ->map(fn (AutonomousExecutionSnapshot $m) => $this->toDto($m))
            ->all();
    }

    public function listRecent(string $workspaceId, int $limit = 100): array
    {
        $limit = max(1, min($limit, 200));

        return AutonomousExecutionSnapshot::query()
            ->where('workspace_id', $workspaceId)
            ->orderByDesc('captured_at')
            ->limit($limit)
            ->get()
            ->map(fn (AutonomousExecutionSnapshot $m) => $this->toDto($m))
            ->all();
    }

    private function toDto(AutonomousExecutionSnapshot $m): AutonomousExecutionSnapshotDto
    {
        return new AutonomousExecutionSnapshotDto(
            id: (string) $m->id,
            workspaceId: (string) $m->workspace_id,
            autonomousWorkflowId: (string) $m->autonomous_workflow_id,
            cycleNumber: (int) $m->cycle_number,
            status: AutonomousExecutionStatus::from((string) $m->status),
            decisionType: AutonomousDecisionType::from((string) $m->decision_type),
            engine: (string) $m->engine,
            focus: (string) $m->focus,
            score: (int) $m->score,
            confidence: $m->confidence !== null ? (float) $m->confidence : null,
            title: (string) $m->title,
            summary: (string) $m->summary,
            rationale: $m->rationale,
            blockedReason: $m->blocked_reason,
            decisionPayload: is_array($m->decision_payload) ? $m->decision_payload : [],
            evidence: is_array($m->evidence) ? $m->evidence : [],
            actionPayload: is_array($m->action_payload) ? $m->action_payload : [],
            personalizationContext: is_array($m->personalization_context) ? $m->personalization_context : [],
            mlFeatures: is_array($m->ml_features) ? $m->ml_features : [],
            recommendationId: $m->recommendation_id,
            scheduledPostId: $m->scheduled_post_id,
            generatedOutputId: $m->generated_output_id,
            capturedAt: $m->captured_at,
            idempotencyKey: (string) $m->idempotency_key,
        );
    }
}
