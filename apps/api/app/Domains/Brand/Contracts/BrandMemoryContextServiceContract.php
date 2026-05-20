<?php

declare(strict_types=1);

namespace App\Domains\Brand\Contracts;

use App\Domains\Brand\Data\BrandMemoryContext;

interface BrandMemoryContextServiceContract
{
    public function forHookAgent(
        string $workspaceId,
        string $hookQueryText,
        ?string $configTargetAudience = null,
        ?string $configContentPillar = null,
        ?int $memoryVersion = null,
    ): BrandMemoryContext;
}
