<?php

declare(strict_types=1);

namespace App\Domains\Recommendations\Services;

use App\Domains\Recommendations\Contracts\MlCompatibilityLayerContract;
use App\Domains\Recommendations\Contracts\RecommendationRepositoryContract;
use App\Domains\Recommendations\Data\CreateRecommendationDto;
use App\Domains\Recommendations\Data\GenerateRecommendationsResultDto;
use App\Domains\Recommendations\Data\RecommendationDto;

/**
 * Core generator: correlation context → specialized recommenders → persist.
 */
final class RecommendationEngine
{
    public function __construct(
        private readonly AnalyticsCorrelationEngine $correlation,
        private readonly HookOptimizationRecommender $hookOptimization,
        private readonly PostingTimeRecommender $postingTime,
        private readonly AudienceFitRecommender $audienceFit,
        private readonly CtaOptimizationRecommender $ctaOptimization,
        private readonly OptimizationOpportunityDetector $opportunities,
        private readonly RecommendationAggregationService $aggregation,
        private readonly RecommendationRepositoryContract $repository,
        private readonly MlCompatibilityLayerContract $mlLayer,
        private readonly RecommendationExecutionLogger $logger,
    ) {
    }

    public function generate(string $workspaceId, ?int $lookbackDays = null): GenerateRecommendationsResultDto
    {
        $context = $this->correlation->buildContext($workspaceId, $lookbackDays);
        $minScore = (int) config('recommendations.min_score_to_persist', 35);

        $drafts = array_merge(
            $this->hookOptimization->recommend($context),
            $this->postingTime->recommend($context),
            $this->audienceFit->recommend($context),
            $this->ctaOptimization->recommend($context),
            $this->opportunities->detect($context),
        );

        $aggregated = $this->aggregation->aggregate($drafts);
        $persisted = [];
        $supersededTotal = 0;

        foreach ($aggregated as $draft) {
            if ($draft->score < $minScore) {
                continue;
            }
            $enriched = new CreateRecommendationDto(
                workspaceId: $draft->workspaceId,
                type: $draft->type,
                source: $draft->source,
                correlationKey: $draft->correlationKey,
                title: $draft->title,
                summary: $draft->summary,
                rationale: $draft->rationale,
                score: $draft->score,
                confidence: $draft->confidence,
                evidence: $draft->evidence,
                personalizationContext: $draft->personalizationContext,
                actionPayload: $draft->actionPayload,
                mlState: $this->mlLayer->enrichState($draft),
                validFrom: $draft->validFrom,
                validUntil: $draft->validUntil,
                idempotencyKey: $draft->idempotencyKey,
            );

            $supersededTotal += $this->repository->supersedeActiveByCorrelationKey(
                $workspaceId,
                $enriched->correlationKey,
            );
            $persisted[] = $this->repository->create($enriched);
        }

        $counts = $this->countByType($persisted);

        $this->logger->info('generated', [
            'workspace_id' => $workspaceId,
            'draft_count' => count($drafts),
            'persisted_count' => count($persisted),
            'superseded_count' => $supersededTotal,
            'snapshot_count' => count($context->snapshots),
        ]);

        return new GenerateRecommendationsResultDto(
            workspaceId: $workspaceId,
            generatedCount: count($persisted),
            supersededCount: $supersededTotal,
            recommendations: $persisted,
            countsByType: $counts,
        );
    }

    /**
     * @param  list<RecommendationDto>  $rows
     * @return array<string, int>
     */
    private function countByType(array $rows): array
    {
        $counts = [];
        foreach ($rows as $row) {
            $k = $row->type->value;
            $counts[$k] = ($counts[$k] ?? 0) + 1;
        }

        return $counts;
    }
}
