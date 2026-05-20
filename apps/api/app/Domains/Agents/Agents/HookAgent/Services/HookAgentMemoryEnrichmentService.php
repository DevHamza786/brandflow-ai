<?php

declare(strict_types=1);

namespace App\Domains\Agents\Agents\HookAgent\Services;

use App\Domains\Agents\Agents\HookAgent\HookAgentConfig;
use App\Domains\Brand\Contracts\BrandMemoryContextServiceContract;
use App\Domains\Brand\Data\BrandMemoryContext;

/**
 * HookAgent-facing memory enrichment — delegates to Brand domain pipeline.
 */
final class HookAgentMemoryEnrichmentService
{
    public function __construct(
        private readonly BrandMemoryContextServiceContract $brandMemoryContext,
    ) {
    }

    public function enrich(
        string $workspaceId,
        string $hookText,
        HookAgentConfig $config,
    ): BrandMemoryContext {
        return $this->brandMemoryContext->forHookAgent(
            workspaceId: $workspaceId,
            hookQueryText: $hookText,
            configTargetAudience: $config->targetAudience,
            configContentPillar: $config->contentPillar,
            memoryVersion: $config->memoryVersion,
        );
    }
}
