<?php

declare(strict_types=1);

namespace App\Domains\Agents\Repositories;

use App\Domains\Agents\Contracts\AgentRunRepositoryContract;
use App\Domains\Agents\Data\AgentResult;
use App\Domains\Agents\Models\AgentRun;
use Illuminate\Support\Carbon;

final class AgentRunRepository implements AgentRunRepositoryContract
{
    public function find(string $workspaceId, string $id): ?AgentRun
    {
        return AgentRun::query()
            ->where('workspace_id', $workspaceId)
            ->whereKey($id)
            ->first();
    }

    public function findOrFail(string $workspaceId, string $id): AgentRun
    {
        return AgentRun::query()
            ->where('workspace_id', $workspaceId)
            ->whereKey($id)
            ->firstOrFail();
    }

    public function findByIdempotencyKey(string $workspaceId, ?string $idempotencyKey): ?AgentRun
    {
        if ($idempotencyKey === null || $idempotencyKey === '') {
            return null;
        }

        return AgentRun::query()
            ->where('workspace_id', $workspaceId)
            ->where('idempotency_key', $idempotencyKey)
            ->first();
    }

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
    ): AgentRun {
        return AgentRun::query()->create([
            'workspace_id' => $workspaceId,
            'slug' => $slug,
            'status' => 'queued',
            'input' => $input,
            'options' => $options,
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    public function markRunning(AgentRun $run): void
    {
        $run->update([
            'status' => 'running',
            'started_at' => Carbon::now(),
        ]);
    }

    public function markCompleted(AgentRun $run, AgentResult $result): void
    {
        $run->update([
            'status' => 'completed',
            'output' => $result->output,
            'trace_id' => $result->traceId,
            'completed_at' => Carbon::now(),
            'error' => null,
        ]);
    }

    public function markFailed(AgentRun $run, string $message, array $context = []): void
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

    public function mergeOptions(AgentRun $run, array $options): AgentRun
    {
        $run->update([
            'options' => array_merge($run->options ?? [], $options),
        ]);

        return $run->fresh() ?? $run;
    }
}
