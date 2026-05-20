<?php

declare(strict_types=1);

namespace App\Domains\Autonomous\Actions;

use App\Domains\Autonomous\Contracts\AutonomousWorkflowRepositoryContract;
use App\Domains\Autonomous\Data\AutonomousWorkflowDto;
use App\Domains\Autonomous\Data\UpdateAutonomousWorkflowDto;

final class UpdateAutonomousWorkflowAction
{
    public function __construct(
        private readonly AutonomousWorkflowRepositoryContract $workflows,
    ) {
    }

    public function execute(
        string $workspaceId,
        string $workflowId,
        UpdateAutonomousWorkflowDto $dto,
    ): AutonomousWorkflowDto {
        return $this->workflows->update($workspaceId, $workflowId, $dto);
    }
}
