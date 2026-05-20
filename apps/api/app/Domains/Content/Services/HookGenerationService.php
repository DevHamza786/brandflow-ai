<?php

declare(strict_types=1);

namespace App\Domains\Content\Services;

use App\Domains\Agents\Agents\HookAgent\Data\HookResult;
use App\Domains\Agents\Agents\HookAgent\Data\HookVariant;
use App\Domains\Agents\Agents\HookAgent\HookAgentConfig;
use App\Domains\Agents\Agents\HookAgent\Support\HookBannedPhraseFilter;
use App\Domains\Agents\Agents\HookAgent\Support\HookPersonalizationLogger;
use App\Domains\Agents\Agents\HookAgent\Support\HookPromptTemplate;
use App\Domains\Agents\Agents\HookAgent\Support\HookResponseParser;
use App\Domains\Agents\Agents\HookAgent\Support\HookResponseValidator;
use App\Domains\AI\Contracts\LlmGateway;
use App\Domains\AI\Contracts\PromptTemplateRegistryContract;
use App\Domains\AI\Data\AiMessage;
use App\Domains\AI\Data\LlmRequest;
use App\Domains\AI\Enums\AiMessageRole;
use App\Domains\Brand\Data\BrandMemoryContext;
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
        private readonly HookPersonalizationLogger $personalizationLogger,
        private readonly HookBannedPhraseFilter $bannedPhraseFilter,
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
        BrandMemoryContext $brandMemory,
        ?string $traceId = null,
    ): array {
        if ($config->maxVariants <= 0) {
            return [];
        }

        $traceId ??= (string) Str::uuid();

        $promptVars = array_merge($brandMemory->promptVariables, [
            'hook_text' => $hookText,
            'primary_score' => $primaryScore->overall,
            'dimensions' => $primaryScore->dimensions->toArray(),
            'suggestions' => $primaryScore->suggestions,
            'max_variants' => $config->maxVariants,
            'target_audience' => $brandMemory->promptVariables['target_audience']
                ?? $config->targetAudience
                ?? 'LinkedIn professionals',
            'content_pillar' => $config->contentPillar ?? '',
            'compact_brand_memory' => $brandMemory->compactBrandSection,
        ]);

        $prompt = $this->prompts->render(
            HookPromptTemplate::GENERATOR_SLUG,
            $promptVars,
            $config->generatorPromptVersion,
        );

        $this->personalizationLogger->logPromptEnrichment(
            'generate_variants',
            $workspaceId,
            $traceId,
            $brandMemory,
            $prompt,
        );

        Log::info('hook.generation.started', [
            'workspace_id' => $workspaceId,
            'trace_id' => $traceId,
            'max_variants' => $config->maxVariants,
            'memory_version' => $brandMemory->memoryVersion,
            'profile_id' => $brandMemory->profileId,
        ]);

        $response = $this->gateway->complete(new LlmRequest(
            workspaceId: $workspaceId,
            provider: $config->resolvedProvider(),
            model: $config->resolvedModel(),
            messages: [
                new AiMessage(AiMessageRole::User, $prompt),
            ],
            structuredOutput: HookPromptTemplate::generatorStructuredOutput(),
            memoryContext: $brandMemory->memoryContextForGateway(),
            traceId: $traceId,
            promptSlug: HookPromptTemplate::GENERATOR_SLUG,
            promptVersion: $config->generatorPromptVersion,
            metadata: [
                'agent' => 'hook',
                'operation' => 'generate_variants',
                'experiment_id' => $config->experimentId,
                'brand_memory' => $brandMemory->toAnalyticsPayload(),
            ],
        ));

        $variants = $this->parser->parseVariants($response, $config->experimentId);
        $variants = $this->bannedPhraseFilter->filterVariants($variants, $brandMemory->bannedPhrases);
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
