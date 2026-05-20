<?php

declare(strict_types=1);

namespace App\Domains\Optimization\Services;

use App\Domains\Analytics\Data\PostPerformanceSnapshotDto;
use App\Domains\Optimization\Data\CreateOptimizationSnapshotDto;
use App\Domains\Optimization\Data\OptimizationContextDto;
use App\Domains\Optimization\Data\OptimizationLoopDto;
use App\Domains\Optimization\Enums\OptimizationSnapshotStatus;
use App\Domains\Optimization\Support\HistoricalComparisonSupport;

final class CtaOptimizationEngine
{
    public const ENGINE = 'cta';

    public function __construct(
        private readonly HistoricalComparisonSupport $comparison,
        private readonly OptimizationScoringService $scoring,
    ) {
    }

    public function analyze(
        OptimizationContextDto $context,
        OptimizationLoopDto $loop,
        int $cycleNumber,
    ): ?CreateOptimizationSnapshotDto {
        $profile = $context->brandProfile;
        if ($profile === null || $profile->preferredCtas === []) {
            return null;
        }

        $withCta = $this->countWithPreferredCta($context->currentPeriodSnapshots, $profile->preferredCtas);
        $withoutCta = count($context->currentPeriodSnapshots) - $withCta;
        if ($withCta < 2 || $withoutCta < 2) {
            return null;
        }

        $avgWith = $this->avgForCtaPresence($context->currentPeriodSnapshots, $profile->preferredCtas, true);
        $avgWithout = $this->avgForCtaPresence($context->currentPeriodSnapshots, $profile->preferredCtas, false);
        $uplift = $this->comparison->upliftPct($avgWith, $avgWithout);

        if ($uplift === null || $uplift < (float) config('optimization.min_uplift_pct', 10.0)) {
            return null;
        }

        $cta = $profile->preferredCtas[0];

        $draft = new CreateOptimizationSnapshotDto(
            workspaceId: $context->workspaceId,
            optimizationLoopId: $loop->id,
            cycleNumber: $cycleNumber,
            engine: self::ENGINE,
            focus: 'cta:'.md5($cta),
            title: 'High-performing CTA pattern',
            summary: sprintf(
                'Posts including preferred CTA “%s” averaged %.1f%% higher engagement than posts without it in the last %d days.',
                $cta,
                $uplift,
                $context->lookbackDays,
            ),
            rationale: 'Compares brand preferred CTAs against normalized engagement in the current optimization window.',
            score: 0,
            confidence: null,
            status: OptimizationSnapshotStatus::Proposed,
            baselineMetrics: ['avg_without_cta' => $avgWithout, 'posts_without_cta' => $withoutCta],
            observedMetrics: ['avg_with_cta' => $avgWith, 'posts_with_cta' => $withCta, 'preferred_cta' => $cta],
            deltaMetrics: ['preferred_cta' => $cta],
            evidence: ['with_cta' => $withCta, 'without_cta' => $withoutCta],
            actionPayload: [
                'action' => 'append_cta',
                'cta' => $cta,
                'alternatives' => array_slice($profile->preferredCtas, 1, 3),
            ],
            personalizationContext: $context->personalizationBase,
            idempotencyKey: $loop->id.':'.$cycleNumber.':'.self::ENGINE.':cta',
        );

        return $this->scoring->apply($draft, $withCta + $withoutCta, $uplift);
    }

    /**
     * @param  list<string>  $preferredCtas
     * @param  list<PostPerformanceSnapshotDto>  $snapshots
     */
    private function countWithPreferredCta(array $snapshots, array $preferredCtas): int
    {
        $n = 0;
        foreach ($snapshots as $s) {
            if ($this->hasPreferredCta($s, $preferredCtas)) {
                $n++;
            }
        }

        return $n;
    }

    /**
     * @param  list<string>  $preferredCtas
     * @param  list<PostPerformanceSnapshotDto>  $snapshots
     */
    private function avgForCtaPresence(array $snapshots, array $preferredCtas, bool $withCta): float
    {
        $vals = [];
        foreach ($snapshots as $s) {
            $has = $this->hasPreferredCta($s, $preferredCtas);
            if ($has !== $withCta || $s->normalizedEngagement === null) {
                continue;
            }
            $vals[] = (float) $s->normalizedEngagement;
        }

        return $vals !== [] ? array_sum($vals) / count($vals) : 0.0;
    }

    /**
     * @param  list<string>  $preferredCtas
     */
    private function hasPreferredCta(PostPerformanceSnapshotDto $snapshot, array $preferredCtas): bool
    {
        $text = is_array($snapshot->hookPerformance) && isset($snapshot->hookPerformance['text'])
            ? mb_strtolower((string) $snapshot->hookPerformance['text'])
            : '';
        foreach ($preferredCtas as $cta) {
            if ($cta !== '' && str_contains($text, mb_strtolower($cta))) {
                return true;
            }
        }

        return false;
    }
}
