<?php

declare(strict_types=1);

namespace App\Domains\Coordination\Services;

use App\Domains\Coordination\Contracts\CoordinationMlCompatibilityLayerContract;
use App\Domains\Coordination\Data\CoordinationTaskDto;
use App\Domains\Coordination\Data\RoutingDecisionDto;
use App\Domains\Coordination\Enums\CoordinationHandlerType;
use App\Domains\Coordination\Enums\CoordinationRole;

/**
 * Maps coordination tasks to agent slugs or integration handlers.
 */
final class AgentRoutingEngine
{
    public function __construct(
        private readonly CoordinationMlCompatibilityLayerContract $mlLayer,
    ) {
    }

    public function resolve(CoordinationTaskDto $task): RoutingDecisionDto
    {
        $roleKey = config(
            'coordination.task_routing.'.$task->taskType->value,
            $task->role->value,
        );

        $role = CoordinationRole::tryFrom((string) $roleKey) ?? $task->role;
        $route = config('coordination.role_routing.'.$role->value, []);

        $handler = CoordinationHandlerType::from((string) ($route['handler'] ?? 'deferred'));
        $agentSlug = isset($route['agent_slug']) ? (string) $route['agent_slug'] : null;
        $fallback = isset($route['fallback_agent_slug']) ? (string) $route['fallback_agent_slug'] : null;

        $decision = new RoutingDecisionDto(
            taskType: $task->taskType,
            role: $role,
            handlerType: $handler,
            agentSlug: $agentSlug,
            fallbackAgentSlug: $fallback ?? $task->fallbackRole,
            priority: $task->priority,
        );

        $enriched = $this->mlLayer->enrichRouting([
            'task_type' => $task->taskType->value,
            'role' => $role->value,
            'agent_slug' => $agentSlug,
        ]);

        if (isset($enriched['agent_slug']) && is_string($enriched['agent_slug'])) {
            return new RoutingDecisionDto(
                taskType: $decision->taskType,
                role: $decision->role,
                handlerType: $decision->handlerType,
                agentSlug: $enriched['agent_slug'],
                fallbackAgentSlug: $decision->fallbackAgentSlug,
                priority: $decision->priority,
            );
        }

        return $decision;
    }
}
