<?php

declare(strict_types=1);

namespace App\Domains\Recommendations\Services;

use App\Domains\Recommendations\Data\CreateRecommendationDto;
use App\Domains\Recommendations\Data\RecommendationContextDto;
use App\Domains\Recommendations\Data\RecommendationEvidenceDto;
use App\Domains\Recommendations\Enums\RecommendationSource;
use App\Domains\Recommendations\Enums\RecommendationType;

final class CtaOptimizationRecommender
{
    public function __construct(
        private readonly RecommendationScoringService $scoring,
    ) {
    }

    /**
     * @return list<CreateRecommendationDto>
     */
    public function recommend(RecommendationContextDto $context): array
    {
        $profile = $context->brandProfile;
        if ($profile === null || $profile->preferredCtas === []) {
            return [];
        }

        $low = array_filter(
            $context->snapshots,
            static fn ($s) => ($s->normalizedEngagement ?? 1) <= $context->p25Normalized,
        );
        if ($low === []) {
            return [];
        }

        $missingCta = 0;
        foreach ($low as $snapshot) {
            $text = is_array($snapshot->hookPerformance) && isset($snapshot->hookPerformance['text'])
                ? mb_strtolower((string) $snapshot->hookPerformance['text'])
                : '';
            $hasCta = false;
            foreach ($profile->preferredCtas as $cta) {
                if ($cta !== '' && str_contains($text, mb_strtolower($cta))) {
                    $hasCta = true;
                    break;
                }
            }
            if (! $hasCta) {
                $missingCta++;
            }
        }

        if ($missingCta < 2) {
            return [];
        }

        $cta = $profile->preferredCtas[0];

        return [
            $this->scoring->apply(
                new CreateRecommendationDto(
                    workspaceId: $context->workspaceId,
                    type: RecommendationType::CtaOptimization,
                    source: RecommendationSource::CtaOptimizer,
                    correlationKey: 'cta:preferred:'.$cta,
                    title: 'Add your preferred CTA to low performers',
                    summary: sprintf(
                        '%d bottom-quartile posts did not include preferred CTA “%s” — top posts in your workspace often close with a clear action.',
                        $missingCta,
                        $cta,
                    ),
                    rationale: 'Matches brand profile preferred CTAs against underperforming hook text.',
                    score: 0,
                    confidence: null,
                    evidence: ['missing_cta_count' => $missingCta],
                    personalizationContext: $context->personalizationBase,
                    actionPayload: [
                        'action' => 'append_cta',
                        'cta' => $cta,
                        'alternatives' => array_slice($profile->preferredCtas, 1, 3),
                    ],
                ),
                new RecommendationEvidenceDto(
                    insightKind: 'cta_gap',
                    sampleSize: $missingCta,
                    baselineValue: null,
                    observedValue: (float) $missingCta,
                    upliftPct: null,
                    metrics: ['preferred_cta' => $cta],
                ),
            ),
        ];
    }
}
