<?php

declare(strict_types=1);

namespace App\Domains\Recommendations\Services;

use App\Domains\Recommendations\Data\CreateRecommendationDto;
use App\Domains\Recommendations\Data\RecommendationEvidenceDto;

final class RecommendationScoringService
{
    public function scoreFromEvidence(RecommendationEvidenceDto $evidence): int
    {
        $sampleBoost = min(40, $evidence->sampleSize * 8);
        $upliftBoost = $evidence->upliftPct !== null
            ? min(45, abs($evidence->upliftPct) * 1.5)
            : 10;

        return (int) min(100, max(0, round(15 + $sampleBoost + $upliftBoost)));
    }

    public function confidenceFromEvidence(RecommendationEvidenceDto $evidence): float
    {
        $sampleFactor = min(1.0, $evidence->sampleSize / 10);
        $upliftFactor = $evidence->upliftPct !== null
            ? min(1.0, abs($evidence->upliftPct) / 50)
            : 0.3;

        return round(min(0.99, 0.2 + ($sampleFactor * 0.5) + ($upliftFactor * 0.3)), 4);
    }

    public function apply(CreateRecommendationDto $draft, RecommendationEvidenceDto $evidence): CreateRecommendationDto
    {
        $score = $this->scoreFromEvidence($evidence);
        $confidence = $this->confidenceFromEvidence($evidence);

        return new CreateRecommendationDto(
            workspaceId: $draft->workspaceId,
            type: $draft->type,
            source: $draft->source,
            correlationKey: $draft->correlationKey,
            title: $draft->title,
            summary: $draft->summary,
            rationale: $draft->rationale,
            score: $score,
            confidence: $confidence,
            evidence: array_merge($draft->evidence, $evidence->toEvidencePayload()),
            personalizationContext: $draft->personalizationContext,
            actionPayload: $draft->actionPayload,
            mlState: $draft->mlState,
            validFrom: $draft->validFrom,
            validUntil: $draft->validUntil,
            idempotencyKey: $draft->idempotencyKey,
        );
    }
}
