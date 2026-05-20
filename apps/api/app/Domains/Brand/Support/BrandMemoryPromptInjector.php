<?php

declare(strict_types=1);

namespace App\Domains\Brand\Support;

use App\Domains\Brand\Data\BrandProfileDto;
use App\Domains\Brand\Data\WritingSampleDto;

/**
 * Builds prompt-template variables from brand memory (provider-agnostic).
 */
final class BrandMemoryPromptInjector
{
    /**
     * @param  list<WritingSampleDto>  $samples
     * @return array<string, mixed>
     */
    public function buildPromptVariables(BrandProfileDto $profile, array $samples = []): array
    {
        $audience = $profile->targetAudience;
        $tone = $profile->toneProfile;
        $style = $profile->styleGuidelines;

        $variables = [
            'brand_voice' => $profile->brandVoice,
            'tone_primary' => $tone->primary,
            'tone_traits' => $tone->traits,
            'tone_avoid' => $tone->avoid,
            'target_audience_summary' => $audience->summary,
            'target_audience_segments' => $audience->segments,
            'target_audience_pain_points' => $audience->painPoints,
            'target_audience_goals' => $audience->goals,
            'banned_phrases' => $profile->bannedPhrases,
            'preferred_ctas' => $profile->preferredCtas,
            'preferred_hook_patterns' => $profile->preferredHookPatterns,
            'style_guidelines_summary' => $style->summary,
            'style_do' => $style->doList,
            'style_dont' => $style->dontList,
            'brand_pillars' => $profile->pillars,
            'memory_version' => $profile->memoryVersion,
        ];

        if ($samples !== []) {
            $variables['writing_sample_excerpts'] = array_map(
                static fn (WritingSampleDto $s) => mb_substr($s->content, 0, 400),
                array_slice($samples, 0, 3)
            );
            $variables['writing_style_signals'] = array_map(
                static fn (WritingSampleDto $s) => $s->normalizedStyleData->toArray(),
                array_slice($samples, 0, 5)
            );
        }

        // Reserved for future experiment / replay metadata
        $variables['experiment_id'] = $profile->metadata['experiment_id'] ?? null;
        $variables['experiment_variant'] = $profile->metadata['experiment_variant'] ?? null;

        return $variables;
    }

    /**
     * System-prompt section for direct injection alongside MemoryContext chunks.
     */
    public function toSystemPromptSection(BrandProfileDto $profile, array $samples = []): string
    {
        $vars = $this->buildPromptVariables($profile, $samples);
        $lines = ['## Brand memory', ''];

        if ($vars['brand_voice'] !== '') {
            $lines[] = '**Voice:** '.$vars['brand_voice'];
        }

        $lines[] = '**Tone:** '.$vars['tone_primary'];
        if ($vars['tone_traits'] !== []) {
            $lines[] = 'Traits: '.implode(', ', $vars['tone_traits']);
        }
        if ($vars['tone_avoid'] !== []) {
            $lines[] = 'Avoid: '.implode(', ', $vars['tone_avoid']);
        }

        if ($vars['target_audience_summary'] !== '') {
            $lines[] = '**Audience:** '.$vars['target_audience_summary'];
        }

        if ($vars['banned_phrases'] !== []) {
            $lines[] = '**Never use:** '.implode('; ', $vars['banned_phrases']);
        }

        if ($vars['preferred_ctas'] !== []) {
            $lines[] = '**Preferred CTAs:** '.implode('; ', $vars['preferred_ctas']);
        }

        if ($vars['preferred_hook_patterns'] !== []) {
            $lines[] = '**Hook patterns:** '.implode('; ', $vars['preferred_hook_patterns']);
        }

        if ($vars['style_guidelines_summary'] !== '') {
            $lines[] = '**Style:** '.$vars['style_guidelines_summary'];
        }

        return trim(implode("\n", $lines));
    }
}
