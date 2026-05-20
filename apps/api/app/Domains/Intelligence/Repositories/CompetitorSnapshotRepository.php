<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Repositories;

use App\Domains\Intelligence\Contracts\CompetitorSnapshotRepositoryContract;
use App\Domains\Intelligence\Data\CompetitorSnapshotDto;
use App\Domains\Intelligence\Models\CompetitorSnapshot;
use Illuminate\Support\Str;

final class CompetitorSnapshotRepository implements CompetitorSnapshotRepositoryContract
{
    public function findById(string $workspaceId, string $id): ?CompetitorSnapshotDto
    {
        $model = CompetitorSnapshot::query()
            ->where('workspace_id', $workspaceId)
            ->where('id', $id)
            ->first();

        return $model ? $this->toDto($model) : null;
    }

    public function findLatestByCompetitor(string $workspaceId, string $competitorId): ?CompetitorSnapshotDto
    {
        $model = CompetitorSnapshot::query()
            ->where('workspace_id', $workspaceId)
            ->where('competitor_id', $competitorId)
            ->orderByDesc('captured_at')
            ->first();

        return $model ? $this->toDto($model) : null;
    }

    public function existsByContentHash(string $workspaceId, string $competitorId, string $contentHash): bool
    {
        return CompetitorSnapshot::query()
            ->where('workspace_id', $workspaceId)
            ->where('competitor_id', $competitorId)
            ->where('content_hash', $contentHash)
            ->exists();
    }

    public function createFromNormalized(array $attributes): CompetitorSnapshotDto
    {
        $model = CompetitorSnapshot::query()->create(array_merge([
            'id' => (string) Str::uuid(),
        ], $attributes));

        return $this->toDto($model);
    }

    public function updateAnalytics(string $workspaceId, string $snapshotId, array $analytics): CompetitorSnapshotDto
    {
        $model = CompetitorSnapshot::query()
            ->where('workspace_id', $workspaceId)
            ->where('id', $snapshotId)
            ->firstOrFail();

        $model->update($analytics);

        return $this->toDto($model->fresh());
    }

    public function listRecentByCompetitor(string $workspaceId, string $competitorId, int $limit = 10): array
    {
        $limit = max(1, min($limit, 50));

        return CompetitorSnapshot::query()
            ->where('workspace_id', $workspaceId)
            ->where('competitor_id', $competitorId)
            ->orderByDesc('captured_at')
            ->limit($limit)
            ->get()
            ->map(fn (CompetitorSnapshot $m) => $this->toDto($m))
            ->all();
    }

    private function toDto(CompetitorSnapshot $m): CompetitorSnapshotDto
    {
        return new CompetitorSnapshotDto(
            id: (string) $m->id,
            workspaceId: (string) $m->workspace_id,
            competitorId: (string) $m->competitor_id,
            capturedAt: $m->captured_at,
            payload: is_array($m->payload) ? $m->payload : [],
            contentHash: $m->content_hash,
            metadata: is_array($m->metadata) ? $m->metadata : [],
            postsCount: (int) $m->posts_count,
            avgEngagementRate: $m->avg_engagement_rate !== null ? (float) $m->avg_engagement_rate : null,
            postsPerWeek: $m->posts_per_week !== null ? (float) $m->posts_per_week : null,
            intelligenceScore: $m->intelligence_score !== null ? (float) $m->intelligence_score : null,
            engagementMetrics: is_array($m->engagement_metrics) ? $m->engagement_metrics : [],
            hookPatterns: is_array($m->hook_patterns) ? $m->hook_patterns : [],
            postingCadence: is_array($m->posting_cadence) ? $m->posting_cadence : [],
            contentStructure: is_array($m->content_structure) ? $m->content_structure : [],
            ctaPatterns: is_array($m->cta_patterns) ? $m->cta_patterns : [],
            trendSummary: is_array($m->trend_summary) ? $m->trend_summary : [],
            mlFeatures: is_array($m->ml_features) ? $m->ml_features : [],
            createdAt: $m->created_at,
        );
    }
}
