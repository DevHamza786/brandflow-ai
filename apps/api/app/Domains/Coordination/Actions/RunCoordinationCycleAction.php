<?php

declare(strict_types=1);

namespace App\Domains\Coordination\Actions;

use App\Domains\Coordination\Data\RunCoordinationCycleResultDto;
use App\Domains\Coordination\Services\MultiAgentCoordinator;

final class RunCoordinationCycleAction
{
    public function __construct(
        private readonly MultiAgentCoordinator $coordinator,
    ) {
    }

    public function execute(string $workspaceId): RunCoordinationCycleResultDto
    {
        return $this->coordinator->runCycle($workspaceId);
    }
}
