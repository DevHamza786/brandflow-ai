<?php

declare(strict_types=1);

namespace App\Domains\Agents\Agents\HookAgent\Support;

use App\Domains\Brand\Data\BrandMemoryContext;
use Illuminate\Support\Facades\Log;

/**
 * Logs compact personalization context before LLM calls (toggle via config).
 */
final class HookPersonalizationLogger
{
    public function logPromptEnrichment(
        string $operation,
        string $workspaceId,
        ?string $traceId,
        BrandMemoryContext $brandMemory,
        string $renderedPromptPreview,
    ): void {
        if (! config('ai.hook_agent.log_prompt_enrichment', false)) {
            return;
        }

        $vars = $brandMemory->promptVariables;

        Log::info('hook.personalization.prompt_context', [
            'operation' => $operation,
            'workspace_id' => $workspaceId,
            'trace_id' => $traceId,
            'profile_id' => $brandMemory->profileId,
            'memory_version' => $brandMemory->memoryVersion,
            'used_fallback' => $brandMemory->usedFallback,
            'tone_primary' => $vars['tone_primary'] ?? null,
            'target_audience' => $vars['target_audience'] ?? null,
            'banned_phrases' => $brandMemory->bannedPhrases,
            'preferred_ctas' => $brandMemory->preferredCtas,
            'preferred_hook_patterns' => $brandMemory->preferredHookPatterns,
            'style_signals' => $brandMemory->styleSignals,
            'compact_section_chars' => strlen($brandMemory->compactBrandSection),
            'prompt_preview_chars' => strlen($renderedPromptPreview),
            'compact_brand_memory' => $brandMemory->compactBrandSection,
        ]);
    }
}
