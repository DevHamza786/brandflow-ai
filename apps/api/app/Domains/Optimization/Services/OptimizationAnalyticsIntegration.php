<?php

declare(strict_types=1);

namespace App\Domains\Optimization\Services;

use App\Domains\Analytics\Contracts\PostPerformanceSnapshotRepositoryContract;
use App\Domains\Brand\Contracts\BrandProfileRepositoryContract;
use App\Domains\Optimization\Data\OptimizationContextDto;
use App\Domains\Optimization\Support\HistoricalComparisonSupport;

/**
 * Builds period-split performance context from analytics snapshots (no raw event scans).
 */
final class OptimizationAnalyticsIntegration
{
    public function __construct(
        private readonly PostPerformanceSnapshotRepositoryContract $snapshots,
        private readonly BrandProfileRepositoryContract $brandProfiles,
        private readonly HistoricalComparisonSupport $comparison,
    ) {
    }

    public function buildContext(string $workspaceId, ?int $lookbackDays = null, ?int $comparisonDays = null): OptimizationContextDto
    {
        $lookbackDays = $lookbackDays ?? (int) config('optimization.lookback_days', 30);
        $comparisonDays = $comparisonDays ?? (int) config('optimization.comparison_days', 30);

        $currentFrom = now()->subDays($lookbackDays)->startOfDay();
        $currentTo = now()->endOfDay();
        $previousFrom = now()->subDays($lookbackDays + $comparisonDays)->startOfDay();
        $previousTo = $currentFrom->copy()->subSecond();

        $current = $this->snapshots->listObservedBetween($workspaceId, $currentFrom, $currentTo);
        $previous = $this->snapshots->listObservedBetween($workspaceId, $previousFrom, $previousTo);

        $profile = $this->brandProfiles->findPrimaryByWorkspace($workspaceId);

        return new OptimizationContextDto(
            workspaceId: $workspaceId,
            lookbackDays: $lookbackDays,
            comparisonDays: $comparisonDays,
            currentPeriodSnapshots: $current,
            previousPeriodSnapshots: $previous,
            currentAvgNormalized: round($this->comparison->avgNormalized($current), 6),
            previousAvgNormalized: round($this->comparison->avgNormalized($previous), 6),
            currentAvgEngagementRate: round($this->comparison->avgEngagementRate($current), 6),
            previousAvgEngagementRate: round($this->comparison->avgEngagementRate($previous), 6),
            brandProfile: $profile,
            personalizationBase: $this->personalizationBase($profile, $lookbackDays, $comparisonDays),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function personalizationBase(?\App\Domains\Brand\Data\BrandProfileDto $profile, int $lookbackDays, int $comparisonDays): array
    {
        if ($profile === null) {
            return [
                'brand_profile_id' => null,
                'audience_segments' => [],
                'lookback_days' => $lookbackDays,
                'comparison_days' => $comparisonDays,
            ];
        }

        return [
            'brand_profile_id' => $profile->id,
            'audience_segments' => $profile->targetAudience->segments,
            'audience_summary' => $profile->targetAudience->summary,
            'tone' => $profile->toneProfile->primary ?? $profile->brandVoice,
            'preferred_hook_patterns' => $profile->preferredHookPatterns,
            'preferred_ctas' => $profile->preferredCtas,
            'lookback_days' => $lookbackDays,
            'comparison_days' => $comparisonDays,
        ];
    }
}
