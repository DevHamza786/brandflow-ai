<?php

declare(strict_types=1);

namespace App\Domains\Recommendations\Services;

use App\Domains\Recommendations\Data\CreateRecommendationDto;
use App\Domains\Recommendations\Data\RecommendationContextDto;
use App\Domains\Recommendations\Data\RecommendationEvidenceDto;
use App\Domains\Recommendations\Enums\RecommendationSource;
use App\Domains\Recommendations\Enums\RecommendationType;

/**
 * Cadence, personalization, and engagement opportunity detection.
 */
final class OptimizationOpportunityDetector
{
    public function __construct(
        private readonly RecommendationScoringService $scoring,
        private readonly HookStyleCorrelationEngine $styleCorrelation,
    ) {
    }

    /**
     * @return list<CreateRecommendationDto>
     */
    public function detect(RecommendationContextDto $context): array
    {
        return array_merge(
            $this->cadenceOpportunities($context),
            $this->personalizationOpportunities($context),
            $this->lowEngagementPostAlerts($context),
        );
    }

    /**
     * @return list<CreateRecommendationDto>
     */
    private function cadenceOpportunities(RecommendationContextDto $context): array
    {
        if ($context->postsPerWeek >= 3) {
            return [];
        }
        if (count($context->snapshots) < 3) {
            return [];
        }

        return [
            $this->scoring->apply(
                new CreateRecommendationDto(
                    workspaceId: $context->workspaceId,
                    type: RecommendationType::PublishingCadence,
                    source: RecommendationSource::OpportunityDetector,
                    correlationKey: 'cadence:increase_frequency',
                    title: 'Increase publishing cadence',
                    summary: sprintf(
                        'You averaged %.1f posts/week over %d days — increasing to 3+/week improves signal for timing and hook correlations.',
                        $context->postsPerWeek,
                        $context->lookbackDays,
                    ),
                    rationale: 'Sparse observations limit posting-time and style confidence.',
                    score: 0,
                    confidence: null,
                    evidence: ['posts_per_week' => $context->postsPerWeek],
                    personalizationContext: $context->personalizationBase,
                    actionPayload: ['action' => 'target_posts_per_week', 'target' => 3],
                ),
                new RecommendationEvidenceDto(
                    insightKind: 'cadence',
                    sampleSize: count($context->snapshots),
                    baselineValue: 3.0,
                    observedValue: $context->postsPerWeek,
                    upliftPct: null,
                ),
            ),
        ];
    }

    /**
     * @return list<CreateRecommendationDto>
     */
    private function personalizationOpportunities(RecommendationContextDto $context): array
    {
        $patterns = $context->personalizationBase['preferred_hook_patterns'] ?? [];
        if (! is_array($patterns) || $patterns === []) {
            return [];
        }

        $under = $this->styleCorrelation->underperformingStyles($context);
        if ($under === []) {
            return [];
        }

        $style = $under[0];

        return [
            $this->scoring->apply(
                new CreateRecommendationDto(
                    workspaceId: $context->workspaceId,
                    type: RecommendationType::Personalization,
                    source: RecommendationSource::AnalyticsCorrelation,
                    correlationKey: 'personalization:avoid_style:'.$style['style'],
                    title: 'Deprioritize '.$style['label'].' for this audience',
                    summary: sprintf(
                        '%s hooks underperform baseline by %.1f%% (n=%d) while brand patterns favor %s.',
                        ucfirst($style['label']),
                        abs($style['uplift_pct']),
                        $style['sample_size'],
                        implode(', ', array_slice($patterns, 0, 2)),
                    ),
                    rationale: 'Combines brand memory preferences with observed style correlation.',
                    score: 0,
                    confidence: null,
                    evidence: [],
                    personalizationContext: $context->personalizationBase,
                    actionPayload: [
                        'action' => 'avoid_hook_style',
                        'style' => $style['style'],
                        'preferred_patterns' => $patterns,
                    ],
                ),
                new RecommendationEvidenceDto(
                    insightKind: 'style_underperform',
                    sampleSize: $style['sample_size'],
                    baselineValue: $context->baselineNormalized,
                    observedValue: $style['avg_normalized'],
                    upliftPct: $style['uplift_pct'],
                    metrics: ['style' => $style['style']],
                ),
            ),
        ];
    }

    /**
     * @return list<CreateRecommendationDto>
     */
    private function lowEngagementPostAlerts(RecommendationContextDto $context): array
    {
        $low = array_filter(
            $context->snapshots,
            static fn ($s) => ($s->normalizedEngagement ?? 1) < $context->p25Normalized
                && $s->impressions >= 100,
        );
        if (count($low) < 2) {
            return [];
        }

        return [
            $this->scoring->apply(
                new CreateRecommendationDto(
                    workspaceId: $context->workspaceId,
                    type: RecommendationType::EngagementImprovement,
                    source: RecommendationSource::AnalyticsCorrelation,
                    correlationKey: 'engagement:low_cluster',
                    title: 'Review low-engagement post cluster',
                    summary: sprintf(
                        '%d posts with 100+ impressions sit below your p25 normalized engagement (%.3f). Audit hooks and posting times before next publish.',
                        count($low),
                        $context->p25Normalized,
                    ),
                    rationale: 'Cluster detection on impressions + normalized engagement — feeds workflow re-run gates.',
                    score: 0,
                    confidence: null,
                    evidence: ['low_post_count' => count($low)],
                    personalizationContext: $context->personalizationBase,
                    actionPayload: ['action' => 'audit_low_engagement', 'count' => count($low)],
                ),
                new RecommendationEvidenceDto(
                    insightKind: 'low_engagement_cluster',
                    sampleSize: count($low),
                    baselineValue: $context->baselineNormalized,
                    observedValue: $context->p25Normalized,
                    upliftPct: null,
                ),
            ),
        ];
    }
}
