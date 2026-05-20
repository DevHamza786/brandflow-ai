<?php

declare(strict_types=1);

namespace App\Domains\Content\Services;

use App\Domains\Agents\Agents\HookAgent\HookAgentConfig;
use App\Domains\Agents\Agents\HookAgent\Support\HookPersonalizationLogger;
use App\Domains\Agents\Agents\HookAgent\Support\HookResponseParser;
use App\Domains\Agents\Agents\HookAgent\Support\HookResponseValidator;
use App\Domains\Agents\Agents\HookAgent\Data\HookResult;
use App\Domains\Agents\Agents\HookAgent\Support\HookPromptTemplate;
use App\Domains\AI\Contracts\LlmGateway;
use App\Domains\AI\Contracts\PromptTemplateRegistryContract;
use App\Domains\AI\Data\AiMessage;
use App\Domains\AI\Data\LlmRequest;
use App\Domains\AI\Enums\AiMessageRole;
use App\Domains\Brand\Data\BrandMemoryContext;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Scores opening lines via LlmGateway + hook.scorer prompt.
 */
final class HookScoringService
{
    public function __construct(
        private readonly LlmGateway $gateway,
        private readonly PromptTemplateRegistryContract $prompts,
        private readonly HookResponseParser $parser,
        private readonly HookResponseValidator $validator,
        private readonly HookPersonalizationLogger $personalizationLogger,
    ) {
    }

    public function score(
        string $workspaceId,
        string $hookText,
        HookAgentConfig $config,
        BrandMemoryContext $brandMemory,
        ?string $traceId = null,
    ): HookResult {
        $this->validator->validateHookText($hookText);

        $traceId ??= (string) Str::uuid();

        $promptVars = array_merge($brandMemory->promptVariables, [
            'hook_text' => $hookText,
            'target_audience' => $brandMemory->promptVariables['target_audience']
                ?? $config->targetAudience
                ?? 'LinkedIn professionals',
            'content_pillar' => $config->contentPillar ?? '',
            'compact_brand_memory' => $brandMemory->compactBrandSection,
        ]);

        $prompt = $this->prompts->render(
            HookPromptTemplate::SCORER_SLUG,
            $promptVars,
            $config->scorerPromptVersion,
        );

        $this->personalizationLogger->logPromptEnrichment(
            'score',
            $workspaceId,
            $traceId,
            $brandMemory,
            $prompt,
        );

        Log::info('hook.scoring.started', [
            'workspace_id' => $workspaceId,
            'trace_id' => $traceId,
            'prompt' => HookPromptTemplate::SCORER_SLUG,
            'experiment_id' => $config->experimentId,
            'memory_version' => $brandMemory->memoryVersion,
            'profile_id' => $brandMemory->profileId,
        ]);

        $gatewayMemory = $brandMemory->memoryContextForGateway();

        $response = $this->gateway->complete(new LlmRequest(
            workspaceId: $workspaceId,
            provider: $config->resolvedProvider(),
            model: $config->resolvedModel(),
            messages: [
                new AiMessage(AiMessageRole::User, $prompt),
            ],
            structuredOutput: HookPromptTemplate::scorerStructuredOutput(),
            memoryContext: $gatewayMemory,
            traceId: $traceId,
            promptSlug: HookPromptTemplate::SCORER_SLUG,
            promptVersion: $config->scorerPromptVersion,
            metadata: [
                'agent' => 'hook',
                'operation' => 'score',
                'experiment_id' => $config->experimentId,
                'brand_memory' => $brandMemory->toAnalyticsPayload(),
            ],
        ));

        $result = $this->parser->parseScorer(
            $response,
            $hookText,
            $config->scorerPromptVersion,
        );

        $this->validator->validateScorerResult($result);

        Log::info('hook.scoring.completed', [
            'workspace_id' => $workspaceId,
            'trace_id' => $response->traceId ?? $traceId,
            'overall' => $result->overall,
            'tokens' => $response->tokenUsage->totalTokens,
        ]);

        return $result;
    }
}
