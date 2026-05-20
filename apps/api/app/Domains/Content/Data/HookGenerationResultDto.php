<?php

declare(strict_types=1);

namespace App\Domains\Content\Data;

use App\Domains\Agents\Models\AgentRun;
use App\Domains\Content\Models\HookScore;
use App\Domains\Shared\Data\DataTransferObject;
use App\Domains\Workflows\Models\WorkflowRun;

/**
 * End-to-end hook generation workflow result (async-first).
 */
final class HookGenerationResultDto extends DataTransferObject
{
    public function __construct(
        public readonly AgentRun $agentRun,
        public readonly WorkflowRun $workflowRun,
        public readonly bool $wasReplayed = false,
        public readonly ?HookScore $hookScore = null,
    ) {
    }
}
