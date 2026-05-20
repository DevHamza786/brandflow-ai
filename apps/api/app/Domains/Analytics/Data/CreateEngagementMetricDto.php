<?php

declare(strict_types=1);

namespace App\Domains\Analytics\Data;

use App\Domains\Shared\Data\DataTransferObject;

/**
 * Lightweight engagement row for `engagement_metrics` (daily grain).
 *
 * @param  array<string, mixed>|null  $dimensions
 * @param  array<string, mixed>  $metadata
 */
final class CreateEngagementMetricDto extends DataTransferObject
{
    public function __construct(
        public readonly string $workspaceId,
        public readonly string $measurableType,
        public readonly string $measurableId,
        public readonly string $metricDate,
        public readonly string $metricType,
        public readonly string $value,
        public readonly ?array $dimensions = null,
        public readonly string $source = 'ingestion',
        public readonly array $metadata = [],
    ) {
    }
}
