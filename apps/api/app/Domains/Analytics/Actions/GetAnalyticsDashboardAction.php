<?php

declare(strict_types=1);

namespace App\Domains\Analytics\Actions;

use App\Domains\Analytics\Data\AnalyticsDashboardDto;
use App\Domains\Analytics\Services\AnalyticsDashboardService;
use Carbon\Carbon;

final class GetAnalyticsDashboardAction
{
    public function __construct(
        private readonly AnalyticsDashboardService $dashboard,
    ) {
    }

    public function execute(
        string $workspaceId,
        ?string $preset,
        ?string $from,
        ?string $to,
    ): AnalyticsDashboardDto {
        [$rangeFrom, $rangeTo, $resolvedPreset] = $this->resolveRange($preset, $from, $to);

        return $this->dashboard->build(
            workspaceId: $workspaceId,
            from: $rangeFrom,
            to: $rangeTo,
            preset: $resolvedPreset,
        );
    }

    /**
     * @return array{0:Carbon,1:Carbon,2:?string}
     */
    private function resolveRange(?string $preset, ?string $from, ?string $to): array
    {
        if ($from !== null && $to !== null) {
            return [
                Carbon::parse($from)->startOfDay(),
                Carbon::parse($to)->endOfDay(),
                $preset,
            ];
        }

        $days = match ($preset) {
            '7d' => 7,
            '90d' => 90,
            '30d', null => 30,
            default => 30,
        };

        return [
            now()->subDays($days - 1)->startOfDay(),
            now()->endOfDay(),
            $preset ?? '30d',
        ];
    }
}
