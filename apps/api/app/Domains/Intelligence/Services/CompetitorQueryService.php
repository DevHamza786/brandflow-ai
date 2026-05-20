<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\Contracts\CompetitorRepositoryContract;
use App\Domains\Intelligence\Contracts\CompetitorSnapshotRepositoryContract;
use App\Domains\Intelligence\Data\CompetitorDto;
use App\Domains\Intelligence\Data\CompetitorIntelligenceReportDto;
use App\Domains\Intelligence\Data\CompetitorSnapshotDto;

final class CompetitorQueryService
{
    public function __construct(
        private readonly CompetitorRepositoryContract $competitors,
        private readonly CompetitorSnapshotRepositoryContract $snapshots,
    ) {
    }

    /**
     * @return list<CompetitorDto>
     */
    public function listCompetitors(string $workspaceId, int $limit = 50): array
    {
        return $this->competitors->listActive($workspaceId, $limit);
    }

    public function intelligenceReport(string $workspaceId, string $competitorId): ?CompetitorIntelligenceReportDto
    {
        $competitor = $this->competitors->findById($workspaceId, $competitorId);
        if ($competitor === null) {
            return null;
        }

        $latest = $this->snapshots->findLatestByCompetitor($workspaceId, $competitorId);

        return new CompetitorIntelligenceReportDto(
            competitor: $competitor,
            latestSnapshot: $latest,
            hookPatternInsights: is_array($latest?->hookPatterns['insights'] ?? null)
                ? $latest->hookPatterns['insights']
                : [],
            benchmark: is_array($latest?->trendSummary['benchmark'] ?? null)
                ? $latest->trendSummary['benchmark']
                : [],
            trends: is_array($latest?->trendSummary) ? $latest->trendSummary : [],
            intelligenceScore: $competitor->intelligenceScore ?? $latest?->intelligenceScore,
            recommendationHints: $this->buildHints($latest),
        );
    }

    public function latestSnapshot(string $workspaceId, string $competitorId): ?CompetitorSnapshotDto
    {
        return $this->snapshots->findLatestByCompetitor($workspaceId, $competitorId);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildHints(?CompetitorSnapshotDto $snapshot): array
    {
        if ($snapshot === null) {
            return [];
        }

        $hints = [];
        foreach ($snapshot->hookPatterns['insights'] ?? [] as $insight) {
            if (is_array($insight) && isset($insight['summary'])) {
                $hints[] = ['type' => 'hook_pattern', 'text' => $insight['summary']];
            }
        }

        $benchmark = $snapshot->trendSummary['benchmark'] ?? null;
        if (is_array($benchmark) && ! empty($benchmark['competitor_ahead'])) {
            $hints[] = [
                'type' => 'benchmark',
                'text' => 'Competitor engagement ahead of workspace baseline',
            ];
        }

        return $hints;
    }
}
