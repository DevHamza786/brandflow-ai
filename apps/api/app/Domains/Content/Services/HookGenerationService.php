<?php

declare(strict_types=1);

namespace App\Domains\Content\Services;

use App\Domains\Agents\Agents\HookAgent\Data\HookResult;
use App\Domains\Agents\Agents\HookAgent\Data\HookVariant;
use App\Domains\Agents\Agents\HookAgent\HookAgentConfig;
use App\Domains\Agents\Agents\HookAgent\Support\HookPromptTemplate;
use App\Domains\Agents\Agents\HookAgent\Support\HookResponseParser;
use App\Domains\Agents\Agents\HookAgent\Support\HookResponseValidator;
use App\Domains\AI\Contracts\LlmGateway;
use App\Domains\AI\Contracts\PromptTemplateRegistryContract;
use App\Domains\AI\Data\AiMessage;
use App\Domains\AI\Data\LlmRequest;
use App\Domains\AI\Data\MemoryContext;
use App\Domains\AI\Enums\AiMessageRole;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Generates hook variants via LlmGateway + hook.variant_generator prompt.
 */
final class HookGenerationService
{
    public function __construct(
        private readonly LlmGateway $gateway,
        private readonly PromptTemplateRegistryContract $prompts,
        private readonly HookResponseParser $parser,
        private readonly HookResponseValidator $validator,
    ) {
    }

    /**
     * @return list<HookVariant>
     */
    public function generate(
        string $workspaceId,
        string $hookText,
        HookResult $primaryScore,
        HookAgentConfig $config,
        MemoryContext $memory,
        ?string $traceId = null,
    ): array {
        if ($config->maxVariants <= 0) {
            return [];
        }

        $traceId ??= (string) Str::uuid();

        $prompt = $this->prompts->render(
            HookPromptTemplate::GENERATOR_SLUG,
            [
                'hook_text' => $hookText,
                'primary_score' => $primaryScore->overall,
                'dimensions' => $primaryScore->dimensions->toArray(),
                'suggestions' => $primaryScore->suggestions,
                'max_variants' => $config->maxVariants,
                'target_audience' => $config->targetAudience ?? 'LinkedIn professionals',
                'content_pillar' => $config->contentPillar ?? '',
                'memory_chunks' => $memory->chunks,
            ],
            $config->generatorPromptVersion,
        );

        Log::info('hook.generation.started', [
            'workspace_id' => $workspaceId,
            'trace_id' => $traceId,
            'max_variants' => $config->maxVariants,
        ]);

        $response = $this->gateway->complete(new LlmRequest(
            workspaceId: $workspaceId,
            provider: $config->resolvedProvider(),
            model: $config->resolvedModel(),
            messages: [
                new AiMessage(AiMessageRole::User, $prompt),
            ],
            structuredOutput: HookPromptTemplate::generatorStructuredOutput(),
            memoryContext: $memory->isEmpty() ? null : $memory,
            traceId: $traceId,
            promptSlug: HookPromptTemplate::GENERATOR_SLUG,
            promptVersion: $config->generatorPromptVersion,
            metadata: [
                'agent' => 'hook',
                'operation' => 'generate_variants',
                'experiment_id' => $config->experimentId,
            ],
        ));

        $variants = $this->parser->parseVariants($response, $config->experimentId);
        $this->validator->validateVariants($variants, $config->maxVariants);

        Log::info('hook.generation.completed', [
            'workspace_id' => $workspaceId,
            'trace_id' => $response->traceId ?? $traceId,
            'variant_count' => count($variants),
            'tokens' => $response->tokenUsage->totalTokens,
        ]);

        return $variants;
    }
}
