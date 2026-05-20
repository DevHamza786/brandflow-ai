<?php

declare(strict_types=1);

namespace App\Domains\Analytics\Contracts;

use App\Domains\Analytics\Data\AnalyticsEventDto;
use App\Domains\Analytics\Data\CreateAnalyticsEventDto;
use App\Domains\Shared\Contracts\WorkspaceScopedRepositoryContract;

interface AnalyticsEventRepositoryContract extends WorkspaceScopedRepositoryContract
{
    public function append(CreateAnalyticsEventDto $dto): AnalyticsEventDto;

    /**
     * @return list<AnalyticsEventDto>
     */
    public function listRecent(string $workspaceId, int $limit = 50): array;
}
