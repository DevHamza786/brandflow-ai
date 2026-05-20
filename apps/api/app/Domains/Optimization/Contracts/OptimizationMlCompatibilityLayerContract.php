<?php

declare(strict_types=1);

namespace App\Domains\Optimization\Contracts;

use App\Domains\Optimization\Data\CreateOptimizationSnapshotDto;

interface OptimizationMlCompatibilityLayerContract
{
    /**
     * @return array<string, mixed>
     */
    public function enrichFeatures(CreateOptimizationSnapshotDto $draft): array;
}
