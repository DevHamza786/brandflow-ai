<?php

declare(strict_types=1);

namespace App\Domains\Recommendations\Services;

use App\Domains\Analytics\Data\PostPerformanceSnapshotDto;
use App\Domains\Recommendations\Data\CreateRecommendationDto;
use App\Domains\Recommendations\Data\RecommendationContextDto;
use App\Domains\Recommendations\Data\RecommendationEvidenceDto;
use App\Domains\Recommendations\Enums\RecommendationSource;
use App\Domains\Recommendations\Enums\RecommendationType;
use App\Domains\Recommendations\Support\HookStyleClassifier;

final class HookOptimizationRecommender
{
    public function __construct(
        private readonly HookStyleCorrelationEngine $styleCorrelation,
        private readonly RecommendationScoringService $scoring,
        private readonly HookStyleClassifier $classifier,
    ) {
    }

    /**
     * @return list<CreateRecommendationDto>
     */
    public function recommend(RecommendationContextDto $context): array
    {
        $out = [];
        $minUplift = (float) config('recommendations.min_uplift_pct', 12.0);
        $top = $this->styleCorrelation->rankedStyles($context);

        if ($top !== [] && $top[0]['uplift_pct'] >= $minUplift) {
            $best = $top[0];
            $segment = $this->audienceLabel($context);
            $out[] = $this->scoring->apply(
                $this->draft(
                    context: $context,
                    key: 'hook_style:best:'.$best['style'],
                    type: RecommendationType::HookStyle,
                    source: RecommendationSource::HookStyleCorrelation,
                    title: 'Lead with '.$best['label'].' hooks',
                    summary: sprintf(
                        '%s hooks average %.1f%% above your workspace baseline (n=%d)%s.',
                        ucfirst($best['label']),
                        $best['uplift_pct'],
                        $best['sample_size'],
                        $segment !== '' ? " for {$segment}" : '',
                    ),
                    rationale: 'Observed normalized engagement from published/scored hooks in your lookback window.',
                    action: [
                        'action' => 'prefer_hook_style',
                        'style' => $best['style'],
                        'label' => $best['label'],
                    ],
                ),
                new RecommendationEvidenceDto(
                    insightKind: 'hook_style_uplift',
                    sampleSize: $best['sample_size'],
                    baselineValue: $context->baselineNormalized,
                    observedValue: $best['avg_normalized'],
                    upliftPct: $best['uplift_pct'],
                    metrics: ['style' => $best['style']],
                ),
            );
        }

        foreach ($this->weakHooks($context) as $weak) {
            $out[] = $this->scoring->apply(
                $this->draft(
                    context: $context,
                    key: 'hook_weak:'.$weak['id'],
                    type: RecommendationType::EngagementImprovement,
                    source: RecommendationSource::OpportunityDetector,
                    title: 'Retire or rewrite underperforming hook',
                    summary: $weak['summary'],
                    rationale: $weak['rationale'],
                    action: [
                        'action' => 'rewrite_hook',
                        'entity_id' => $weak['entity_id'],
                        'suggested_style' => $weak['suggested_style'],
                    ],
                ),
                $weak['evidence'],
            );
        }

        return $out;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function weakHooks(RecommendationContextDto $context): array
    {
        $p25 = $context->p25Normalized;
        $bestStyle = $this->styleCorrelation->rankedStyles($context)[0]['style'] ?? HookStyleClassifier::STYLE_QUESTION;
        $weak = [];

        foreach ($context->snapshots as $snapshot) {
            $norm = $snapshot->normalizedEngagement;
            if ($norm === null || $norm > $p25) {
                continue;
            }
            $lab = is_array($snapshot->hookPerformance) && isset($snapshot->hookPerformance['overall'])
                ? (float) $snapshot->hookPerformance['overall']
                : null;
            if ($lab !== null && $lab < 60) {
                continue;
            }

            $text = is_array($snapshot->hookPerformance) && isset($snapshot->hookPerformance['text'])
                ? (string) $snapshot->hookPerformance['text']
                : 'this hook';
            $style = $this->classifier->classify($text);

            $weak[] = [
                'id' => $snapshot->id,
                'entity_id' => $snapshot->entityId,
                'summary' => sprintf(
                    '“%s” normalized at %.3f (bottom quartile). Lab score was strong but live engagement lagged.',
                    mb_strlen($text) > 60 ? mb_substr($text, 0, 60).'…' : $text,
                    $norm,
                ),
                'rationale' => 'High lab / low live gap suggests audience or timing mismatch — not generic copy advice.',
                'suggested_style' => $bestStyle,
                'evidence' => new RecommendationEvidenceDto(
                    insightKind: 'weak_hook',
                    sampleSize: 1,
                    baselineValue: $context->baselineNormalized,
                    observedValue: $norm,
                    upliftPct: (($norm - $context->baselineNormalized) / max(0.0001, $context->baselineNormalized)) * 100,
                    sampleEntityIds: [$snapshot->entityId],
                    metrics: ['lab_overall' => $lab, 'style' => $style],
                ),
            ];
        }

        return array_slice($weak, 0, 3);
    }

    /**
     * @param  array<string, mixed>  $action
     */
    private function draft(
        RecommendationContextDto $context,
        string $key,
        RecommendationType $type,
        RecommendationSource $source,
        string $title,
        string $summary,
        string $rationale,
        array $action,
    ): CreateRecommendationDto {
        return new CreateRecommendationDto(
            workspaceId: $context->workspaceId,
            type: $type,
            source: $source,
            correlationKey: $key,
            title: $title,
            summary: $summary,
            rationale: $rationale,
            score: 0,
            confidence: null,
            evidence: [],
            personalizationContext: $context->personalizationBase,
            actionPayload: $action,
        );
    }

    private function audienceLabel(RecommendationContextDto $context): string
    {
        $segments = $context->personalizationBase['audience_segments'] ?? [];
        if (! is_array($segments) || $segments === []) {
            return '';
        }

        return implode(', ', array_slice($segments, 0, 2));
    }
}
