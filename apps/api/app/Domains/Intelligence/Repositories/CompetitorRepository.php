<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Repositories;

use App\Domains\Intelligence\Contracts\CompetitorRepositoryContract;
use App\Domains\Intelligence\Data\CompetitorDto;
use App\Domains\Intelligence\Data\CreateCompetitorDto;
use App\Domains\Intelligence\Models\Competitor;
use Illuminate\Support\Str;

final class CompetitorRepository implements CompetitorRepositoryContract
{
    public function create(CreateCompetitorDto $dto): CompetitorDto
    {
        $model = Competitor::query()->create([
            'id' => (string) Str::uuid(),
            'workspace_id' => $dto->workspaceId,
            'linkedin_url' => $dto->linkedinUrl,
            'name' => $dto->name,
            'linkedin_urn' => $dto->linkedinUrn,
            'labels' => $dto->labels,
            'metadata' => $dto->metadata,
            'scrape_cadence_hours' => max(1, $dto->scrapeCadenceHours),
            'is_active' => true,
        ]);

        return $this->toDto($model);
    }

    public function findById(string $workspaceId, string $id): ?CompetitorDto
    {
        $model = Competitor::query()
            ->where('workspace_id', $workspaceId)
            ->where('id', $id)
            ->first();

        return $model ? $this->toDto($model) : null;
    }

    public function listActive(string $workspaceId, int $limit = 50): array
    {
        $limit = max(1, min($limit, 100));

        return Competitor::query()
            ->where('workspace_id', $workspaceId)
            ->where('is_active', true)
            ->orderByDesc('intelligence_score')
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(fn (Competitor $m) => $this->toDto($m))
            ->all();
    }

    public function updateIntelligenceScore(string $workspaceId, string $competitorId, float $score): void
    {
        Competitor::query()
            ->where('workspace_id', $workspaceId)
            ->where('id', $competitorId)
            ->update([
                'intelligence_score' => round($score, 4),
                'last_analyzed_at' => now(),
            ]);
    }

    private function toDto(Competitor $m): CompetitorDto
    {
        return new CompetitorDto(
            id: (string) $m->id,
            workspaceId: (string) $m->workspace_id,
            linkedinUrl: (string) $m->linkedin_url,
            name: $m->name,
            linkedinUrn: $m->linkedin_urn,
            labels: is_array($m->labels) ? $m->labels : [],
            metadata: is_array($m->metadata) ? $m->metadata : [],
            scrapeCadenceHours: (int) $m->scrape_cadence_hours,
            lastScrapedAt: $m->last_scraped_at,
            lastAnalyzedAt: $m->last_analyzed_at,
            intelligenceScore: $m->intelligence_score !== null ? (float) $m->intelligence_score : null,
            isActive: (bool) $m->is_active,
            createdAt: $m->created_at,
        );
    }
}
