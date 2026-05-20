<?php

declare(strict_types=1);

namespace App\Domains\Analytics\Repositories;

use App\Domains\Analytics\Contracts\EngagementMetricRepositoryContract;
use App\Domains\Analytics\Data\CreateEngagementMetricDto;
use App\Domains\Analytics\Models\EngagementMetric;

final class EngagementMetricRepository implements EngagementMetricRepositoryContract
{
    public function upsertDaily(CreateEngagementMetricDto $dto): void
    {
        EngagementMetric::query()->updateOrCreate(
            [
                'workspace_id' => $dto->workspaceId,
                'measurable_type' => $dto->measurableType,
                'measurable_id' => $dto->measurableId,
                'metric_date' => $dto->metricDate,
                'metric_type' => $dto->metricType,
            ],
            [
                'value' => $dto->value,
                'dimensions' => $dto->dimensions,
                'source' => $dto->source,
                'metadata' => $dto->metadata,
                'captured_at' => now(),
            ],
        );
    }
}
