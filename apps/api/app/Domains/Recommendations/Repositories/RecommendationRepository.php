<?php

declare(strict_types=1);

namespace App\Domains\Recommendations\Repositories;

use App\Domains\Recommendations\Contracts\RecommendationRepositoryContract;
use App\Domains\Recommendations\Data\CreateRecommendationDto;
use App\Domains\Recommendations\Data\RecommendationDto;
use App\Domains\Recommendations\Enums\RecommendationSource;
use App\Domains\Recommendations\Enums\RecommendationStatus;
use App\Domains\Recommendations\Enums\RecommendationType;
use App\Domains\Recommendations\Models\Recommendation;
use Illuminate\Support\Str;

final class RecommendationRepository implements RecommendationRepositoryContract
{
    public function create(CreateRecommendationDto $dto): RecommendationDto
    {
        $model = Recommendation::query()->create([
            'id' => (string) Str::uuid(),
            'workspace_id' => $dto->workspaceId,
            'type' => $dto->type->value,
            'status' => RecommendationStatus::Active->value,
            'source' => $dto->source->value,
            'correlation_key' => $dto->correlationKey,
            'title' => $dto->title,
            'summary' => $dto->summary,
            'rationale' => $dto->rationale,
            'score' => max(0, min(100, $dto->score)),
            'confidence' => $dto->confidence,
            'evidence' => $dto->evidence,
            'personalization_context' => $dto->personalizationContext,
            'action_payload' => $dto->actionPayload,
            'ml_state' => $dto->mlState,
            'generated_at' => now(),
            'valid_from' => $dto->validFrom,
            'valid_until' => $dto->validUntil,
            'idempotency_key' => $dto->idempotencyKey,
        ]);

        return $this->toDto($model);
    }

    public function findById(string $workspaceId, string $id): ?RecommendationDto
    {
        $model = Recommendation::query()
            ->where('workspace_id', $workspaceId)
            ->where('id', $id)
            ->first();

        return $model ? $this->toDto($model) : null;
    }

    public function listActive(
        string $workspaceId,
        ?RecommendationType $type = null,
        int $limit = 50,
        int $minScore = 0,
    ): array {
        $limit = max(1, min($limit, 100));

        $q = Recommendation::query()
            ->where('workspace_id', $workspaceId)
            ->where('status', RecommendationStatus::Active->value)
            ->where('score', '>=', $minScore)
            ->orderByDesc('score')
            ->orderByDesc('generated_at')
            ->limit($limit);

        if ($type !== null) {
            $q->where('type', $type->value);
        }

        return $q->get()->map(fn (Recommendation $m) => $this->toDto($m))->all();
    }

    public function supersedeActiveByCorrelationKey(string $workspaceId, string $correlationKey): int
    {
        return Recommendation::query()
            ->where('workspace_id', $workspaceId)
            ->where('correlation_key', $correlationKey)
            ->where('status', RecommendationStatus::Active->value)
            ->update(['status' => RecommendationStatus::Superseded->value]);
    }

    public function countActive(string $workspaceId): int
    {
        return Recommendation::query()
            ->where('workspace_id', $workspaceId)
            ->where('status', RecommendationStatus::Active->value)
            ->count();
    }

    private function toDto(Recommendation $m): RecommendationDto
    {
        return new RecommendationDto(
            id: (string) $m->id,
            workspaceId: (string) $m->workspace_id,
            type: RecommendationType::from((string) $m->type),
            status: RecommendationStatus::from((string) $m->status),
            source: RecommendationSource::from((string) $m->source),
            correlationKey: (string) $m->correlation_key,
            title: (string) $m->title,
            summary: (string) $m->summary,
            rationale: $m->rationale,
            score: (int) $m->score,
            confidence: $m->confidence !== null ? (float) $m->confidence : null,
            evidence: is_array($m->evidence) ? $m->evidence : [],
            personalizationContext: is_array($m->personalization_context) ? $m->personalization_context : [],
            actionPayload: is_array($m->action_payload) ? $m->action_payload : [],
            mlState: is_array($m->ml_state) ? $m->ml_state : [],
            generatedAt: $m->generated_at,
            validFrom: $m->valid_from,
            validUntil: $m->valid_until,
            supersededById: $m->superseded_by_id,
            createdAt: $m->created_at,
        );
    }
}
