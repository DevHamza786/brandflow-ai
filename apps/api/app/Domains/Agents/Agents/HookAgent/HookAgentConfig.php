<?php

declare(strict_types=1);

namespace App\Domains\Agents\Agents\HookAgent;

use App\Domains\Agents\Data\AgentContext;
use App\Domains\Shared\Data\DataTransferObject;

/**
 * HookAgent runtime configuration (from AgentContext + defaults).
 */
final class HookAgentConfig extends DataTransferObject
{
    public function __construct(
        public readonly string $contentVersionId,
        public readonly int $maxVariants = 3,
        public readonly ?string $targetAudience = null,
        public readonly ?string $contentPillar = null,
        public readonly ?string $provider = null,
        public readonly ?string $model = null,
        public readonly string $scorerPromptVersion = 'v1',
        public readonly string $generatorPromptVersion = 'v1',
        public readonly ?string $experimentId = null,
        public readonly ?int $memoryVersion = null,
    ) {
    }

    public static function fromAgentContext(AgentContext $context): self
    {
        return new self(
            contentVersionId: (string) $context->input('content_version_id', ''),
            maxVariants: (int) $context->option('max_variants', 3),
            targetAudience: $context->option('target_audience'),
            contentPillar: $context->option('content_pillar'),
            provider: $context->option('provider'),
            model: $context->option('model'),
            scorerPromptVersion: (string) $context->option('scorer_prompt_version', 'v1'),
            generatorPromptVersion: (string) $context->option('generator_prompt_version', 'v1'),
            experimentId: $context->option('experiment_id'),
            memoryVersion: $context->option('memory_version') !== null
                ? (int) $context->option('memory_version')
                : null,
        );
    }

    public function resolvedProvider(): string
    {
        return $this->provider ?? (string) config('ai.default_provider', 'openai');
    }

    public function resolvedModel(): string
    {
        if ($this->model !== null) {
            return $this->model;
        }

        $provider = $this->resolvedProvider();

        return (string) config("ai.providers.{$provider}.default_model");
    }
}
