<?php

declare(strict_types=1);

namespace App\Domains\Coordination\Services;

use App\Domains\Brand\Contracts\MemoryRetrievalServiceContract;
use App\Domains\Coordination\Data\AgentCoordinationDto;
use App\Domains\Coordination\Data\SharedCoordinationContextDto;

/**
 * Builds reference-only shared context (no duplicated prompt bodies).
 */
final class AgentContextOrchestrator
{
    public function __construct(
        private readonly MemoryRetrievalServiceContract $memory,
        private readonly CoordinationAnalyticsIntegration $analytics,
        private readonly CoordinationOptimizationIntegration $optimization,
        private readonly WorkflowSharingEngine $workflowSharing,
    ) {
    }

    public function build(string $workspaceId, AgentCoordinationDto $coordination): SharedCoordinationContextDto
    {
        $query = 'multi-agent coordination cycle '.$coordination->currentCycle;
        $memoryContext = $this->memory->retrieve(
            workspaceId: $workspaceId,
            query: $query,
            types: ['voice', 'facts', 'performance'],
            limit: 5,
        );

        $chunkIds = [];
        foreach ($memoryContext->chunks as $chunk) {
            $chunkIds[] = $chunk->id;
        }

        $analyticsRefs = $this->analytics->buildContextRefs($workspaceId);
        $optimizationRefs = $this->optimization->buildContextRefs($workspaceId);
        $workflowRefs = $this->workflowSharing->buildContextRefs($coordination);

        $digest = hash('sha256', json_encode([
            'memory' => $chunkIds,
            'analytics' => $analyticsRefs,
            'optimization' => $optimizationRefs,
            'workflow' => $workflowRefs,
        ], JSON_THROW_ON_ERROR));

        return new SharedCoordinationContextDto(
            workspaceId: $workspaceId,
            memoryVersion: $memoryContext->memoryVersion,
            memoryChunkIds: $chunkIds,
            analyticsRefs: $analyticsRefs,
            optimizationRefs: $optimizationRefs,
            workflowRefs: $workflowRefs,
            contextDigest: $digest,
        );
    }
}
