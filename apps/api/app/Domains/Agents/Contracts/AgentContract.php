<?php

declare(strict_types=1);

namespace App\Domains\Agents\Contracts;

use App\Domains\Agents\Data\AgentContext;
use App\Domains\Agents\Data\AgentResult;

/**
 * Contract for all PBOS autonomous agents.
 *
 * @see docs/AGENTS.md §4 Agent Catalog
 */
interface AgentContract
{
    public function slug(): string;

    public function run(AgentContext $context): AgentResult;
}
