<?php

declare(strict_types=1);

namespace App\Domains\Analytics\Contracts;

use App\Domains\Analytics\Data\CreateEngagementMetricDto;
use App\Domains\Shared\Contracts\WorkspaceScopedRepositoryContract;

interface EngagementMetricRepositoryContract extends WorkspaceScopedRepositoryContract
{
    /**
     * Upsert one daily metric row (unique on workspace + subject + date + metric_type).
     */
    public function upsertDaily(CreateEngagementMetricDto $dto): void;
}
