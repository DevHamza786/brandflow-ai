<?php

declare(strict_types=1);

namespace App\Domains\Analytics\Repositories;

use App\Domains\Analytics\Contracts\PostPerformanceSnapshotRepositoryContract;
use App\Domains\Analytics\Data\CreatePostPerformanceSnapshotDto;
use App\Domains\Analytics\Data\PostPerformanceSnapshotDto;
use App\Domains\Analytics\Models\PostPerformanceSnapshot;
use Illuminate\Support\Facades\DB;

final class PostPerformanceSnapshotRepository implements PostPerformanceSnapshotRepositoryContract
{
    public function create(CreatePostPerformanceSnapshotDto $dto): PostPerformanceSnapshotDto
    {
        $model = PostPerformanceSnapshot::query()->create([
            'workspace_id' => $dto->workspaceId,
            'entity_type' => $dto->entityType,
            'entity_id' => $dto->entityId,
            'provider_post_id' => $dto->providerPostId,
            'posted_at' => $dto->postedAt,
            'observed_at' => $dto->observedAt,
            'impressions' => $dto->impressions,
            'likes' => $dto->likes,
            'comments' => $dto->comments,
            'reposts' => $dto->reposts,
            'saves' => $dto->saves,
            'engagement_rate' => null,
            'normalized_engagement' => null,
            'hook_performance' => $dto->hookPerformance,
            'content_features' => $dto->contentFeatures,
            'ml_features' => $dto->mlFeatures,
            'metadata' => $dto->metadata,
            'engagement_rate' => $dto->engagementRate,
            'normalized_engagement' => $dto->normalizedEngagement,
        ]);

        return $this->toDto($model);
    }

    public function findById(string $workspaceId, string $id): ?PostPerformanceSnapshotDto
    {
        $model = PostPerformanceSnapshot::query()
            ->where('workspace_id', $workspaceId)
            ->where('id', $id)
            ->first();

        return $model ? $this->toDto($model) : null;
    }

    public function topByNormalizedEngagement(string $workspaceId, int $limit = 20): array
    {
        $limit = max(1, min($limit, 100));

        $models = PostPerformanceSnapshot::query()
            ->where('workspace_id', $workspaceId)
            ->whereNotNull('normalized_engagement')
            ->orderByDesc('normalized_engagement')
            ->orderByDesc('observed_at')
            ->limit($limit)
            ->get();

        return $models->map(fn (PostPerformanceSnapshot $m) => $this->toDto($m))->all();
    }

    public function listObservedBetween(
        string $workspaceId,
        \DateTimeInterface $from,
        \DateTimeInterface $to,
        int $limit = 5000,
    ): array {
        $limit = max(1, min($limit, 10000));

        $models = PostPerformanceSnapshot::query()
            ->where('workspace_id', $workspaceId)
            ->where('observed_at', '>=', $from)
            ->where('observed_at', '<=', $to)
            ->orderBy('observed_at')
            ->limit($limit)
            ->get();

        return $models->map(fn (PostPerformanceSnapshot $m) => $this->toDto($m))->all();
    }

    public function listRecentForWorkspace(
        string $workspaceId,
        int $daysBack = 90,
        int $limit = 500,
    ): array {
        $daysBack = max(1, min($daysBack, 365));
        $limit = max(1, min($limit, 5000));

        return $this->listObservedBetween(
            $workspaceId,
            now()->subDays($daysBack)->startOfDay(),
            now()->endOfDay(),
            $limit,
        );
    }

    public function postingHourHistogram(string $workspaceId, int $daysBack = 30): array
    {
        $daysBack = max(1, min($daysBack, 365));
        $since = now()->subDays($daysBack);

        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            $rows = DB::select(
                <<<'SQL'
                SELECT EXTRACT(HOUR FROM posted_at AT TIME ZONE 'UTC')::int AS hour,
                       COUNT(*)::int AS sample_count,
                       COALESCE(AVG(normalized_engagement), 0)::float AS avg_normalized
                FROM post_performance_snapshots
                WHERE workspace_id = ?
                  AND posted_at IS NOT NULL
                  AND posted_at >= ?
                GROUP BY 1
                ORDER BY 1
                SQL,
                [$workspaceId, $since],
            );

            return array_map(static fn ($r) => [
                'hour' => (int) $r->hour,
                'sample_count' => (int) $r->sample_count,
                'avg_normalized' => (float) $r->avg_normalized,
            ], $rows);
        }

        // SQLite / others: PHP-side bucket (fine for foundation volumes).
        $models = PostPerformanceSnapshot::query()
            ->where('workspace_id', $workspaceId)
            ->whereNotNull('posted_at')
            ->where('posted_at', '>=', $since)
            ->get(['posted_at', 'normalized_engagement']);

        $buckets = [];
        foreach ($models as $m) {
            if ($m->posted_at === null) {
                continue;
            }
            $h = (int) $m->posted_at->format('G');
            if (! isset($buckets[$h])) {
                $buckets[$h] = ['sum' => 0.0, 'n' => 0];
            }
            $buckets[$h]['sum'] += (float) ($m->normalized_engagement ?? 0);
            $buckets[$h]['n']++;
        }

        $out = [];
        foreach ($buckets as $hour => $agg) {
            $out[] = [
                'hour' => (int) $hour,
                'sample_count' => $agg['n'],
                'avg_normalized' => $agg['n'] > 0 ? $agg['sum'] / $agg['n'] : 0.0,
            ];
        }
        usort($out, static fn ($a, $b) => $a['hour'] <=> $b['hour']);

        return $out;
    }

    private function toDto(PostPerformanceSnapshot $m): PostPerformanceSnapshotDto
    {
        return new PostPerformanceSnapshotDto(
            id: (string) $m->id,
            workspaceId: (string) $m->workspace_id,
            entityType: (string) $m->entity_type,
            entityId: (string) $m->entity_id,
            providerPostId: $m->provider_post_id,
            postedAt: $m->posted_at,
            observedAt: $m->observed_at,
            impressions: (int) $m->impressions,
            likes: (int) $m->likes,
            comments: (int) $m->comments,
            reposts: (int) $m->reposts,
            saves: (int) $m->saves,
            engagementRate: $m->engagement_rate !== null ? (float) $m->engagement_rate : null,
            normalizedEngagement: $m->normalized_engagement !== null ? (float) $m->normalized_engagement : null,
            hookPerformance: is_array($m->hook_performance) ? $m->hook_performance : null,
            contentFeatures: is_array($m->content_features) ? $m->content_features : null,
            mlFeatures: is_array($m->ml_features) ? $m->ml_features : null,
            metadata: is_array($m->metadata) ? $m->metadata : [],
            createdAt: $m->created_at,
        );
    }
}
