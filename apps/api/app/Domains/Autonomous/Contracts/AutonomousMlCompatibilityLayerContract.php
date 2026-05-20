<?php

declare(strict_types=1);

namespace App\Domains\Autonomous\Contracts;

use App\Domains\Autonomous\Data\CreateAutonomousExecutionSnapshotDto;

interface AutonomousMlCompatibilityLayerContract
{
    /**
     * @return array<string, mixed>
     */
    public function enrichFeatures(CreateAutonomousExecutionSnapshotDto $draft): array;
}
