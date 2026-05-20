<?php

declare(strict_types=1);

namespace App\Domains\Analytics\Data;

use App\Domains\Shared\Data\DataTransferObject;

/**
 * Dashboard read model — serialized to API (no dashboards in domain UI).
 *
 * @param  array{from:string,to:string,preset:?string,label:string}  $range
 * @param  array<string, int|float|null>  $kpis
 * @param  list<array<string, mixed>>  $engagementSeries
 * @param  list<array<string, mixed>>  $scoreTrend
 * @param  list<array<string, mixed>>  $postingFrequency
 * @param  list<array{hour:int,sample_count:int,avg_normalized:float}>  $postingTime
 * @param  list<array<string, mixed>>  $topHooks
 * @param  array<string, mixed>  $audienceOverview
 * @param  array<string, mixed>  $comparison
 */
final class AnalyticsDashboardDto extends DataTransferObject
{
    public function __construct(
        public readonly array $range,
        public readonly array $kpis,
        public readonly array $engagementSeries,
        public readonly array $scoreTrend,
        public readonly array $postingFrequency,
        public readonly array $postingTime,
        public readonly array $topHooks,
        public readonly array $audienceOverview,
        public readonly array $comparison,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'range' => $this->range,
            'kpis' => $this->kpis,
            'engagement_series' => $this->engagementSeries,
            'score_trend' => $this->scoreTrend,
            'posting_frequency' => $this->postingFrequency,
            'posting_time' => $this->postingTime,
            'top_hooks' => $this->topHooks,
            'audience_overview' => $this->audienceOverview,
            'comparison' => $this->comparison,
        ];
    }
}
