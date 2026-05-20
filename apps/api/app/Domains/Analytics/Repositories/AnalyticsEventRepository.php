<?php

declare(strict_types=1);

namespace App\Domains\Analytics\Repositories;

use App\Domains\Analytics\Contracts\AnalyticsEventRepositoryContract;
use App\Domains\Analytics\Data\AnalyticsEventDto;
use App\Domains\Analytics\Data\CreateAnalyticsEventDto;
use App\Domains\Analytics\Models\AnalyticsEvent;
use Illuminate\Database\Eloquent\Builder;

final class AnalyticsEventRepository implements AnalyticsEventRepositoryContract
{
    public function append(CreateAnalyticsEventDto $dto): AnalyticsEventDto
    {
        if ($dto->idempotencyKey !== null && $dto->idempotencyKey !== '') {
            $existing = AnalyticsEvent::query()
                ->where('workspace_id', $dto->workspaceId)
                ->where('idempotency_key', $dto->idempotencyKey)
                ->first();

            if ($existing !== null) {
                return $this->toDto($existing);
            }
        }

        $model = AnalyticsEvent::query()->create([
            'workspace_id' => $dto->workspaceId,
            'event_type' => $dto->eventType,
            'entity_type' => $dto->entityType,
            'entity_id' => $dto->entityId,
            'properties' => $dto->properties,
            'occurred_at' => $dto->occurredAt ?? now(),
            'idempotency_key' => $dto->idempotencyKey,
            'user_id' => $dto->userId,
            'session_id' => $dto->sessionId,
        ]);

        return $this->toDto($model);
    }

    public function listRecent(string $workspaceId, int $limit = 50): array
    {
        $limit = max(1, min($limit, 200));

        return $this->scoped($workspaceId)
            ->orderByDesc('occurred_at')
            ->limit($limit)
            ->get()
            ->map(fn (AnalyticsEvent $m) => $this->toDto($m))
            ->all();
    }

    private function toDto(AnalyticsEvent $m): AnalyticsEventDto
    {
        return new AnalyticsEventDto(
            id: (string) $m->id,
            workspaceId: (string) $m->workspace_id,
            eventType: (string) $m->event_type,
            entityType: $m->entity_type,
            entityId: $m->entity_id,
            properties: is_array($m->properties) ? $m->properties : [],
            occurredAt: $m->occurred_at,
            idempotencyKey: $m->idempotency_key,
            userId: $m->user_id !== null ? (int) $m->user_id : null,
            sessionId: $m->session_id,
            createdAt: $m->created_at,
        );
    }

    /**
     * @return Builder<AnalyticsEvent>
     */
    private function scoped(string $workspaceId): Builder
    {
        return AnalyticsEvent::query()->where('workspace_id', $workspaceId);
    }
}
