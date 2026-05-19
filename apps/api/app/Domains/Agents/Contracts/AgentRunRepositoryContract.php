<?php

declare(strict_types=1);

namespace App\Domains\Agents\Contracts;

use App\Domains\Agents\Data\AgentResult;
use App\Domains\Agents\Models\AgentRun;
use App\Domains\Shared\Contracts\WorkspaceScopedRepositoryContract;

interface AgentRunRepositoryContract extends WorkspaceScopedRepositoryContract
{
    public function find(string $workspaceId, string $id): ?AgentRun;

    public function findOrFail(string $workspaceId, string $id): AgentRun;

    /**
     * @param  array<string, mixed>  $input
     * @param  array<string, mixed>  $options
     */
    public function createQueued(
        string $workspaceId,
        string $slug,
        array $input,
        array $options = [],
        ?string $idempotencyKey = null,
    ): AgentRun;

    public function markRunning(AgentRun $run): void;

    public function markCompleted(AgentRun $run, AgentResult $result): void;

    public function markFailed(AgentRun $run, string $message, array $context = []): void;
}
