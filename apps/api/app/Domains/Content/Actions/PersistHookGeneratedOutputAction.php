<?php

declare(strict_types=1);

namespace App\Domains\Content\Actions;

use App\Domains\Agents\Agents\HookAgent\Data\HookCollection;
use App\Domains\Agents\Agents\HookAgent\HookAgentConfig;
use App\Domains\Agents\Data\AgentContext;
use App\Domains\AI\Data\MemoryContext;
use App\Domains\Brand\Data\BrandMemoryContext;
use App\Domains\Content\Models\HookScore;
use App\Domains\Content\Services\HookGeneratedOutputPersistenceService;

/**
 * Transactional persistence of hook scores + generated_outputs.
 *
 * @return array{hook_score: HookScore, generated_output_id: string}
 */
final class PersistHookGeneratedOutputAction
{
    public function __construct(
        private readonly HookGeneratedOutputPersistenceService $persistence,
    ) {
    }

    public function execute(
        AgentContext $context,
        HookAgentConfig $config,
        HookCollection $collection,
        MemoryContext $memory,
        string $generatedOutputId,
        ?BrandMemoryContext $brandMemory = null,
    ): array {
        $result = $this->persistence->persistHookResults(
            $context,
            $config,
            $collection,
            $memory,
            $generatedOutputId,
            brandMemory: $brandMemory,
        );

        return [
            'hook_score' => $result['hook_score'],
            'generated_output_id' => $result['generated_output']->id,
        ];
    }
}
