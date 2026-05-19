<?php

declare(strict_types=1);

namespace App\Domains\Content\Services;

use App\Domains\Agents\Agents\HookAgent\Data\HookResult;
use App\Domains\Agents\Agents\HookAgent\HookAgentConfig;
use App\Domains\Agents\Agents\HookAgent\Support\HookPromptTemplate;
use App\Domains\Agents\Agents\HookAgent\Support\HookResponseParser;
use App\Domains\Agents\Agents\HookAgent\Support\HookResponseValidator;
use App\Domains\AI\Contracts\LlmGateway;
use App\Domains\AI\Contracts\PromptTemplateRegistryContract;
use App\Domains\AI\Data\AiMessage;
use App\Domains\AI\Data\LlmRequest;
use App\Domains\AI\Enums\AiMessageRole;
use App\Domains\AI\Data\MemoryContext;
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
    ) {
    }

    public function score(
        string $workspaceId,
        string $hookText,
        HookAgentConfig $config,
        MemoryContext $memory,
        ?string $traceId = null,
    ): HookResult {
        $this->validator->validateHookText($hookText);

        $traceId ??= (string) Str::uuid();

        $prompt = $this->prompts->render(
            HookPromptTemplate::SCORER_SLUG,
            [
                'hook_text' => $hookText,
                'target_audience' => $config->targetAudience ?? 'LinkedIn professionals',
                'content_pillar' => $config->contentPillar ?? '',
                'memory_chunks' => $memory->chunks,
            ],
            $config->scorerPromptVersion,
        );

        Log::info('hook.scoring.started', [
            'workspace_id' => $workspaceId,
            'trace_id' => $traceId,
            'prompt' => HookPromptTemplate::SCORER_SLUG,
            'experiment_id' => $config->experimentId,
        ]);

        $response = $this->gateway->complete(new LlmRequest(
            workspaceId: $workspaceId,
            provider: $config->resolvedProvider(),
            model: $config->resolvedModel(),
            messages: [
                new AiMessage(AiMessageRole::User, $prompt),
            ],
            structuredOutput: HookPromptTemplate::scorerStructuredOutput(),
            memoryContext: $memory->isEmpty() ? null : $memory,
            traceId: $traceId,
            promptSlug: HookPromptTemplate::SCORER_SLUG,
            promptVersion: $config->scorerPromptVersion,
            metadata: [
                'agent' => 'hook',
                'operation' => 'score',
                'experiment_id' => $config->experimentId,
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
