<?php

declare(strict_types=1);

namespace App\Domains\Coordination\Services;

use App\Domains\Coordination\Contracts\AgentCoordinationRepositoryContract;
use App\Domains\Coordination\Contracts\AgentCoordinationSnapshotRepositoryContract;
use App\Domains\Coordination\Contracts\CoordinationMlCompatibilityLayerContract;
use App\Domains\Coordination\Data\CreateCoordinationSnapshotDto;
use App\Domains\Coordination\Data\RunCoordinationCycleResultDto;
use App\Domains\Coordination\Enums\CoordinationSnapshotStatus;
use App\Domains\Coordination\Enums\CoordinationSnapshotType;
use App\Domains\Coordination\Enums\CoordinationStatus;
use App\Domains\Coordination\Events\AgentCoordinationDispatched;
use App\Domains\Coordination\Events\AgentCoordinationFailed;
use App\Domains\Coordination\Events\CoordinationCycleCompleted;
use App\Domains\Coordination\Events\CoordinationCycleStarted;
use App\Domains\Coordination\Events\CoordinationRecovered;
use App\Domains\Coordination\Models\AgentCoordination;
use Illuminate\Support\Str;

/**
 * Orchestrates multi-agent cycles with failure isolation and shared context refs.
 */
final class MultiAgentCoordinator
{
    public function __construct(
        private readonly AgentCoordinationRepositoryContract $coordinations,
        private readonly AgentCoordinationSnapshotRepositoryContract $snapshots,
        private readonly AgentPriorityEngine $priority,
        private readonly AgentRoutingEngine $routing,
        private readonly AgentContextOrchestrator $contextOrchestrator,
        private readonly InterAgentCommunicationLayer $communication,
        private readonly CoordinationMlCompatibilityLayerContract $mlLayer,
        private readonly CoordinationExecutionLogger $logger,
    ) {
    }

    public function runCycle(string $workspaceId): RunCoordinationCycleResultDto
    {
        $coordination = $this->coordinations->findOrCreateDefault($workspaceId);
        $lockToken = (string) Str::uuid();

        if (! $this->coordinations->tryAcquireLock($workspaceId, $coordination->id, $lockToken)) {
            throw new \RuntimeException('Coordination session is locked by another execution.');
        }

        $traceId = 'coord_'.Str::uuid()->toString();

        try {
            $coordination = $this->coordinations->incrementCycle($workspaceId, $coordination->id);
            $cycle = $coordination->currentCycle;

            event(new CoordinationCycleStarted($workspaceId, $coordination->id, $cycle, $traceId));

            $shared = $this->contextOrchestrator->build($workspaceId, $coordination);
            $this->coordinations->updateSharedContext(
                $workspaceId,
                $coordination->id,
                $shared->toRefsArray(),
            );

            $this->snapshots->create(new CreateCoordinationSnapshotDto(
                workspaceId: $workspaceId,
                agentCoordinationId: $coordination->id,
                snapshotType: CoordinationSnapshotType::ContextShare,
                cycleNumber: $cycle,
                status: CoordinationSnapshotStatus::Completed,
                contextRefs: $shared->toRefsArray(),
                payload: ['digest' => $shared->contextDigest],
                idempotencyKey: "coord:{$coordination->id}:cycle:{$cycle}:context",
                traceId: $traceId,
            ));

            $tasks = $this->priority->order(
                $this->priority->buildDefaultCycleTasks(),
                $coordination->coordinationMode,
            );

            $completed = [];
            $failed = [];
            $recovered = 0;
            $snapshotsCreated = 1;

            foreach ($tasks as $task) {
                $taskKey = $task->taskType->value;
                $started = hrtime(true);

                try {
                    $decision = $this->routing->resolve($task);

                    $this->snapshots->create(new CreateCoordinationSnapshotDto(
                        workspaceId: $workspaceId,
                        agentCoordinationId: $coordination->id,
                        snapshotType: CoordinationSnapshotType::Routing,
                        cycleNumber: $cycle,
                        status: CoordinationSnapshotStatus::Routed,
                        roleSlug: $decision->role->value,
                        taskType: $taskKey,
                        routedAgentSlug: $decision->agentSlug,
                        handlerType: $decision->handlerType,
                        contextRefs: $shared->toRefsArray(),
                        payload: ['handler' => $decision->handlerType->value],
                        idempotencyKey: "coord:{$coordination->id}:cycle:{$cycle}:route:{$taskKey}",
                        traceId: $traceId,
                        priority: $decision->priority,
                    ));
                    $snapshotsCreated++;

                    if (in_array($taskKey, config('coordination.test_force_fail_tasks', []), true)) {
                        throw new \RuntimeException("Forced failure for task [{$taskKey}] (test hook).");
                    }

                    $result = $this->communication->execute(
                        $workspaceId,
                        $decision,
                        $shared,
                        $task->input,
                    );

                    $durationMs = (int) ((hrtime(true) - $started) / 1_000_000);

                    $this->snapshots->create(new CreateCoordinationSnapshotDto(
                        workspaceId: $workspaceId,
                        agentCoordinationId: $coordination->id,
                        snapshotType: CoordinationSnapshotType::AgentComplete,
                        cycleNumber: $cycle,
                        status: CoordinationSnapshotStatus::Completed,
                        roleSlug: $decision->role->value,
                        taskType: $taskKey,
                        routedAgentSlug: $decision->agentSlug,
                        handlerType: $decision->handlerType,
                        contextRefs: $shared->toRefsArray(),
                        payload: $result,
                        idempotencyKey: "coord:{$coordination->id}:cycle:{$cycle}:complete:{$taskKey}",
                        traceId: $traceId,
                        agentRunId: $result['agent_run_id'] ?? null,
                        priority: $decision->priority,
                        durationMs: $durationMs,
                    ));
                    $snapshotsCreated++;

                    event(new AgentCoordinationDispatched(
                        $workspaceId,
                        $coordination->id,
                        $taskKey,
                        $decision->agentSlug,
                    ));

                    $completed[] = $taskKey;
                } catch (\Throwable $e) {
                    $durationMs = (int) ((hrtime(true) - $started) / 1_000_000);
                    $failed[] = $taskKey;

                    $this->snapshots->create(new CreateCoordinationSnapshotDto(
                        workspaceId: $workspaceId,
                        agentCoordinationId: $coordination->id,
                        snapshotType: CoordinationSnapshotType::AgentFailed,
                        cycleNumber: $cycle,
                        status: CoordinationSnapshotStatus::Failed,
                        roleSlug: $task->role->value,
                        taskType: $taskKey,
                        contextRefs: $shared->toRefsArray(),
                        error: [
                            'message' => $e->getMessage(),
                            'class' => $e::class,
                        ],
                        idempotencyKey: "coord:{$coordination->id}:cycle:{$cycle}:fail:{$taskKey}",
                        traceId: $traceId,
                        priority: $task->priority,
                        durationMs: $durationMs,
                    ));
                    $snapshotsCreated++;

                    event(new AgentCoordinationFailed($workspaceId, $coordination->id, $taskKey, $e->getMessage()));

                    if ($task->isolated && (bool) config('coordination.failure_isolation', true)) {
                        $recovered += $this->attemptRecovery(
                            $workspaceId,
                            $coordination->id,
                            $cycle,
                            $task,
                            $shared,
                            $traceId,
                        );
                        $this->logger->warning('task.failed.isolated', [
                            'task' => $taskKey,
                            'message' => $e->getMessage(),
                        ]);
                        continue;
                    }

                    throw $e;
                }
            }

            $finalStatus = $failed === []
                ? CoordinationStatus::Active->value
                : CoordinationStatus::PartialSuccess->value;

            $this->coordinations->updateStatus($workspaceId, $coordination->id, $finalStatus);

            $model = AgentCoordination::query()->find($coordination->id);
            if ($model !== null) {
                $model->update([
                    'ml_state' => $this->mlLayer->afterCycle($model->ml_state ?? [], [
                        'cycle' => $cycle,
                        'completed' => count($completed),
                        'failed' => count($failed),
                    ]),
                ]);
            }

            $result = new RunCoordinationCycleResultDto(
                coordinationId: $coordination->id,
                cycleNumber: $cycle,
                snapshotsCreated: $snapshotsCreated,
                tasksCompleted: count($completed),
                tasksFailed: count($failed),
                tasksRecovered: $recovered,
                completedTasks: $completed,
                failedTasks: $failed,
                traceId: $traceId,
            );

            event(new CoordinationCycleCompleted($workspaceId, $coordination->id, $result));

            return $result;
        } finally {
            $this->coordinations->releaseLock($workspaceId, $coordination->id, $lockToken);
        }
    }

    private function attemptRecovery(
        string $workspaceId,
        string $coordinationId,
        int $cycle,
        \App\Domains\Coordination\Data\CoordinationTaskDto $task,
        \App\Domains\Coordination\Data\SharedCoordinationContextDto $shared,
        string $traceId,
    ): int {
        $fallbackRole = $task->fallbackRole;
        if ($fallbackRole === null) {
            return 0;
        }

        $this->snapshots->create(new CreateCoordinationSnapshotDto(
            workspaceId: $workspaceId,
            agentCoordinationId: $coordinationId,
            snapshotType: CoordinationSnapshotType::Recovery,
            cycleNumber: $cycle,
            status: CoordinationSnapshotStatus::Recovered,
            roleSlug: $task->role->value,
            taskType: $task->taskType->value,
            payload: ['fallback_role' => $fallbackRole],
            idempotencyKey: "coord:{$coordinationId}:cycle:{$cycle}:recovery:{$task->taskType->value}",
            traceId: $traceId,
        ));

        event(new CoordinationRecovered($workspaceId, $coordinationId, $task->taskType->value));

        return 1;
    }
}
