<?php

declare(strict_types=1);

namespace App\Domains\Brand\Contracts;

use App\Domains\Brand\Data\BrandMemoryEnrichmentDto;
use App\Domains\Brand\Data\BrandProfileDto;

interface BrandMemoryQueryServiceContract
{
    public function findPrimaryProfile(string $workspaceId): ?BrandProfileDto;

    public function enrichForWorkspace(
        string $workspaceId,
        ?string $query = null,
        int $sampleLimit = 5,
    ): BrandMemoryEnrichmentDto;
}
