<?php

declare(strict_types=1);

namespace App\Domains\Workflows\Services;

use App\Domains\Workflows\Contracts\WorkflowExecutionTrackerContract;
use App\Domains\Workflows\Data\WorkflowExecutionState;
use Illuminate\Contracts\Redis\Factory as RedisFactory;

/**
 * Stores workflow run state in Redis: {env}:pbos:{workspace_id}:workflow:run:{run_id}
 */
final class WorkflowExecutionTracker implements WorkflowExecutionTrackerContract
{
    public function __construct(
        private readonly RedisFactory $redis,
    ) {
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function initialize(
        string $workspaceId,
        string $workflowRunId,
        string $status,
        array $context = [],
    ): void {
        $this->connection()->hMSet($this->key($workspaceId, $workflowRunId), [
            'workflow_run_id' => $workflowRunId,
            'workspace_id' => $workspaceId,
            'status' => $status,
            'context' => json_encode($context, JSON_THROW_ON_ERROR),
            'completed_steps' => json_encode([], JSON_THROW_ON_ERROR),
            'current_step_id' => '',
            'error' => '',
            'updated_at' => now()->toIso8601String(),
        ]);

        $this->expire($workspaceId, $workflowRunId);
    }

    public function get(string $workspaceId, string $workflowRunId): ?WorkflowExecutionState
    {
        $data = $this->connection()->hGetAll($this->key($workspaceId, $workflowRunId));

        if ($data === [] || ! isset($data['workflow_run_id'])) {
            return null;
        }

        return new WorkflowExecutionState(
            workflowRunId: $data['workflow_run_id'],
            workspaceId: $data['workspace_id'],
            status: $data['status'],
            context: json_decode($data['context'] ?: '{}', true, 512, JSON_THROW_ON_ERROR),
            completedSteps: json_decode($data['completed_steps'] ?: '[]', true, 512, JSON_THROW_ON_ERROR),
            currentStepId: ($data['current_step_id'] ?? '') !== '' ? $data['current_step_id'] : null,
            error: ($data['error'] ?? '') !== '' ? $data['error'] : null,
        );
    }

    public function markStepRunning(string $workspaceId, string $workflowRunId, string $stepId): void
    {
        $this->connection()->hMSet($this->key($workspaceId, $workflowRunId), [
            'current_step_id' => $stepId,
            'status' => 'running',
            'updated_at' => now()->toIso8601String(),
        ]);

        $this->expire($workspaceId, $workflowRunId);
    }

    public function markStepCompleted(string $workspaceId, string $workflowRunId, string $stepId): void
    {
        $state = $this->get($workspaceId, $workflowRunId);
        $completed = $state?->completedSteps ?? [];

        if (! in_array($stepId, $completed, true)) {
            $completed[] = $stepId;
        }

        $this->connection()->hMSet($this->key($workspaceId, $workflowRunId), [
            'completed_steps' => json_encode($completed, JSON_THROW_ON_ERROR),
            'current_step_id' => '',
            'updated_at' => now()->toIso8601String(),
        ]);

        $this->expire($workspaceId, $workflowRunId);
    }

    public function markStepFailed(string $workspaceId, string $workflowRunId, string $stepId, string $error): void
    {
        $this->connection()->hMSet($this->key($workspaceId, $workflowRunId), [
            'status' => 'failed',
            'current_step_id' => $stepId,
            'error' => $error,
            'updated_at' => now()->toIso8601String(),
        ]);

        $this->expire($workspaceId, $workflowRunId);
    }

    public function updateStatus(string $workspaceId, string $workflowRunId, string $status, ?string $error = null): void
    {
        $this->connection()->hMSet($this->key($workspaceId, $workflowRunId), [
            'status' => $status,
            'error' => $error ?? '',
            'updated_at' => now()->toIso8601String(),
        ]);

        $this->expire($workspaceId, $workflowRunId);
    }

    public function forget(string $workspaceId, string $workflowRunId): void
    {
        $this->connection()->del($this->key($workspaceId, $workflowRunId));
    }

    private function key(string $workspaceId, string $workflowRunId): string
    {
        $prefix = config('queues.workflow.key_prefix')
            ?? config('app.env', 'local').':pbos';

        return sprintf('%s:%s:workflow:run:%s', $prefix, $workspaceId, $workflowRunId);
    }

    private function expire(string $workspaceId, string $workflowRunId): void
    {
        $ttl = (int) config('queues.workflow.state_ttl', 604800);

        $this->connection()->expire($this->key($workspaceId, $workflowRunId), $ttl);
    }

    private function connection(): \Illuminate\Redis\Connections\Connection
    {
        return $this->redis->connection(config('queues.redis_connection', 'default'));
    }
}
