<?php

declare(strict_types=1);

namespace App\Domains\Coordination\Services;

use App\Domains\Coordination\Data\CoordinationTaskDto;
use App\Domains\Coordination\Enums\CoordinationMode;
use App\Domains\Coordination\Enums\CoordinationRole;
use App\Domains\Coordination\Enums\CoordinationTaskType;

/**
 * Orders tasks for sequential / strategist-led coordination modes.
 */
final class AgentPriorityEngine
{
    /**
     * @return list<CoordinationTaskDto>
     */
    public function buildDefaultCycleTasks(): array
    {
        $tasks = [];
        foreach (config('coordination.default_cycle_tasks', []) as $row) {
            $taskType = CoordinationTaskType::tryFrom((string) ($row['task_type'] ?? ''));
            $role = CoordinationRole::tryFrom((string) ($row['role'] ?? ''));
            if ($taskType === null || $role === null) {
                continue;
            }
            $tasks[] = new CoordinationTaskDto(
                taskType: $taskType,
                role: $role,
                priority: (int) ($row['priority'] ?? 100),
                isolated: true,
            );
        }

        return $tasks;
    }

    /**
     * @param  list<CoordinationTaskDto>  $tasks
     * @return list<CoordinationTaskDto>
     */
    public function order(array $tasks, CoordinationMode $mode): array
    {
        $sorted = $tasks;
        usort($sorted, fn (CoordinationTaskDto $a, CoordinationTaskDto $b) => $a->priority <=> $b->priority);

        if ($mode === CoordinationMode::StrategistLed) {
            $strategist = array_values(array_filter(
                $sorted,
                fn (CoordinationTaskDto $t) => $t->role === CoordinationRole::Strategist,
            ));
            $rest = array_values(array_filter(
                $sorted,
                fn (CoordinationTaskDto $t) => $t->role !== CoordinationRole::Strategist,
            ));

            return [...$strategist, ...$rest];
        }

        return $sorted;
    }
}
