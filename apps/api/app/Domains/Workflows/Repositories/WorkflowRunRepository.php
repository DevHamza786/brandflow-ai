<?php

declare(strict_types=1);

namespace App\Domains\Workflows\Repositories;

use App\Domains\Workflows\Contracts\WorkflowRunRepositoryContract;
use App\Domains\Workflows\Models\WorkflowRun;
use Illuminate\Support\Carbon;

final class WorkflowRunRepository implements WorkflowRunRepositoryContract
{
    public function find(string $workspaceId, string $id): ?WorkflowRun
    {
        return WorkflowRun::query()
            ->where('workspace_id', $workspaceId)
            ->whereKey($id)
            ->first();
    }

    public function findOrFail(string $workspaceId, string $id): WorkflowRun
    {
        return WorkflowRun::query()
            ->where('workspace_id', $workspaceId)
            ->whereKey($id)
            ->firstOrFail();
    }

    public function createQueued(
        string $workspaceId,
        string $workflowId,
        array $context = [],
        ?string $idempotencyKey = null,
    ): WorkflowRun {
        return WorkflowRun::query()->create([
            'workspace_id' => $workspaceId,
            'workflow_id' => $workflowId,
            'status' => 'queued',
            'context' => $context,
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    public function findByIdempotencyKey(string $workspaceId, ?string $idempotencyKey): ?WorkflowRun
    {
        if ($idempotencyKey === null || $idempotencyKey === '') {
            return null;
        }

        return WorkflowRun::query()
            ->where('workspace_id', $workspaceId)
            ->where('idempotency_key', $idempotencyKey)
            ->first();
    }

    public function mergeContext(WorkflowRun $run, array $context): WorkflowRun
    {
        $run->update([
            'context' => array_merge($run->context ?? [], $context),
        ]);

        return $run->fresh() ?? $run;
    }

    public function markRunning(WorkflowRun $run, ?string $currentStepId = null): void
    {
        $run->update([
            'status' => 'running',
            'current_step_id' => $currentStepId,
            'started_at' => $run->started_at ?? Carbon::now(),
        ]);
    }

    public function markCompleted(WorkflowRun $run, array $context = []): void
    {
        $run->update([
            'status' => 'completed',
            'context' => array_merge($run->context ?? [], $context),
            'current_step_id' => null,
            'completed_at' => Carbon::now(),
            'error' => null,
        ]);
    }

    public function markFailed(WorkflowRun $run, string $message, array $context = []): void
    {
        $run->update([
            'status' => 'failed',
            'error' => [
                'message' => $message,
                'context' => $context,
            ],
            'completed_at' => Carbon::now(),
        ]);
    }
}
