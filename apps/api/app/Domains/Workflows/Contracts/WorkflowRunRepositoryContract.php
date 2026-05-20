<?php

declare(strict_types=1);

namespace App\Domains\Workflows\Contracts;

use App\Domains\Shared\Contracts\WorkspaceScopedRepositoryContract;
use App\Domains\Workflows\Models\WorkflowRun;

interface WorkflowRunRepositoryContract extends WorkspaceScopedRepositoryContract
{
    public function find(string $workspaceId, string $id): ?WorkflowRun;

    public function findOrFail(string $workspaceId, string $id): WorkflowRun;

    /**
     * @param  array<string, mixed>  $context
     */
    public function createQueued(
        string $workspaceId,
        string $workflowId,
        array $context = [],
        ?string $idempotencyKey = null,
    ): WorkflowRun;

    public function findByIdempotencyKey(string $workspaceId, ?string $idempotencyKey): ?WorkflowRun;

    /**
     * @param  array<string, mixed>  $context
     */
    public function mergeContext(WorkflowRun $run, array $context): WorkflowRun;

    public function markRunning(WorkflowRun $run, ?string $currentStepId = null): void;

    /**
     * @param  array<string, mixed>  $context
     */
    public function markCompleted(WorkflowRun $run, array $context = []): void;

    public function markFailed(WorkflowRun $run, string $message, array $context = []): void;
}
