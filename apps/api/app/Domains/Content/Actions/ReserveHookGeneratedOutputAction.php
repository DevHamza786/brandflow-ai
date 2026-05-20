<?php

declare(strict_types=1);

namespace App\Domains\Content\Actions;

use App\Domains\Agents\Agents\HookAgent\HookAgentConfig;
use App\Domains\Agents\Data\AgentContext;
use App\Domains\AI\Data\GeneratedOutputDto;
use App\Domains\Content\Services\HookGeneratedOutputPersistenceService;

final class ReserveHookGeneratedOutputAction
{
    public function __construct(
        private readonly HookGeneratedOutputPersistenceService $persistence,
    ) {
    }

    public function execute(
        AgentContext $context,
        HookAgentConfig $config,
        string $workflowRunId,
    ): GeneratedOutputDto {
        return $this->persistence->reserveForWorkflow($context, $config, $workflowRunId);
    }
}
