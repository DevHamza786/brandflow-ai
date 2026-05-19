<?php

declare(strict_types=1);

namespace App\Domains\Agents\Contracts;

use App\Domains\Agents\Data\AgentContext;

/**
 * Tool callable by agents during a run.
 */
interface AgentToolContract
{
    public function slug(): string;

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    public function handle(AgentContext $context, array $arguments): array;
}
