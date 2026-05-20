<?php

declare(strict_types=1);

namespace App\Domains\Brand\Support;

use App\Domains\Brand\Data\AudienceProfileDto;
use App\Domains\Brand\Data\BrandMemoryEnrichmentDto;
use App\Domains\Brand\Data\BrandProfileDto;
use App\Domains\Brand\Data\NormalizedStyleDataDto;
use App\Domains\Brand\Data\WritingSampleDto;

/**
 * Compact prompt context for Hook Lab — avoids duplicating full chunk dumps in Blade.
 */
final class HookBrandMemoryPromptComposer
{
    /**
     * @param  list<WritingSampleDto>  $samples
     * @return array{compact_section: string, variables: array<string, mixed>, style_signals: array<string, mixed>}
     */
    public function compose(
        BrandMemoryEnrichmentDto $enrichment,
        ?string $configTargetAudience,
        ?string $configContentPillar,
        array $samples,
        int $maxSectionChars,
        int $maxSampleExcerptChars,
    ): array {
        $profile = $enrichment->profile;

        if ($profile === null) {
            return [
                'compact_section' => '',
                'variables' => $this->fallbackVariables($configTargetAudience, $configContentPillar),
                'style_signals' => [],
            ];
        }

        $audience = $this->resolveAudience($profile->targetAudience, $configTargetAudience);
        $tone = $profile->toneProfile;
        $style = $profile->styleGuidelines;

        $lines = ['## Brand personalization'];

        if ($profile->brandVoice !== '') {
            $lines[] = '**Voice:** '.$profile->brandVoice;
        }

        $lines[] = '**Tone:** '.$tone->primary;
        if ($tone->traits !== []) {
            $lines[] = 'Traits: '.implode(', ', array_slice($tone->traits, 0, 6));
        }
        if ($tone->avoid !== []) {
            $lines[] = 'Avoid tones: '.implode(', ', array_slice($tone->avoid, 0, 4));
        }

        if ($audience !== '') {
            $lines[] = '**Audience:** '.$audience;
        }

        if ($profile->bannedPhrases !== []) {
            $lines[] = '**Never use phrases:** '.implode('; ', array_slice($profile->bannedPhrases, 0, 12));
        }

        if ($profile->preferredCtas !== []) {
            $lines[] = '**Preferred CTAs:** '.implode('; ', array_slice($profile->preferredCtas, 0, 5));
        }

        if ($profile->preferredHookPatterns !== []) {
            $lines[] = '**Hook patterns:** '.implode('; ', array_slice($profile->preferredHookPatterns, 0, 5));
        }

        if ($style->summary !== '') {
            $lines[] = '**Style:** '.$style->summary;
        }

        if ($configContentPillar !== null && $configContentPillar !== '') {
            $lines[] = '**Content pillar (run):** '.$configContentPillar;
        }

        $styleSignals = $this->aggregateStyleSignals($samples);
        if ($styleSignals !== []) {
            $lines[] = '**Writing style signals:** '.$this->formatStyleSignals($styleSignals);
        }

        foreach (array_slice($samples, 0, 2) as $index => $sample) {
            $excerpt = mb_substr(trim($sample->content), 0, $maxSampleExcerptChars);
            if ($excerpt !== '') {
                $lines[] = '**Sample '.($index + 1).':** "'.$excerpt.'"';
            }
        }

        $compact = $this->truncateSection(implode("\n", $lines), $maxSectionChars);

        $variables = [
            'brand_voice' => $profile->brandVoice,
            'tone_primary' => $tone->primary,
            'tone_traits' => $tone->traits,
            'tone_avoid' => $tone->avoid,
            'target_audience' => $audience,
            'target_audience_summary' => $profile->targetAudience->summary,
            'banned_phrases' => $profile->bannedPhrases,
            'preferred_ctas' => $profile->preferredCtas,
            'preferred_hook_patterns' => $profile->preferredHookPatterns,
            'style_guidelines_summary' => $style->summary,
            'style_do' => $style->doList,
            'style_dont' => $style->dontList,
            'compact_brand_memory' => $compact,
            'content_pillar' => $configContentPillar ?? '',
            'memory_version' => $profile->memoryVersion,
        ];

        return [
            'compact_section' => $compact,
            'variables' => $variables,
            'style_signals' => $styleSignals,
        ];
    }

    private function resolveAudience(AudienceProfileDto $profile, ?string $configOverride): string
    {
        if ($configOverride !== null && trim($configOverride) !== '') {
            return trim($configOverride);
        }

        if ($profile->summary !== '') {
            return $profile->summary;
        }

        if ($profile->segments !== []) {
            return implode(', ', array_slice($profile->segments, 0, 4));
        }

        return 'LinkedIn professionals';
    }

    /**
     * @param  list<WritingSampleDto>  $samples
     * @return array<string, mixed>
     */
    private function aggregateStyleSignals(array $samples): array
    {
        if ($samples === []) {
            return [];
        }

        $lengths = [];
        $questions = 0;
        $firstPerson = 0;

        foreach ($samples as $sample) {
            $style = $sample->normalizedStyleData;
            if ($style->avgSentenceLength > 0) {
                $lengths[] = $style->avgSentenceLength;
            }
            if ($style->usesQuestions) {
                $questions++;
            }
            if ($style->usesFirstPerson) {
                $firstPerson++;
            }
        }

        $count = count($samples);

        return [
            'avg_sentence_length' => $lengths !== [] ? round(array_sum($lengths) / count($lengths), 1) : null,
            'uses_questions' => $questions > 0,
            'uses_first_person' => $firstPerson > ($count / 2),
            'sample_count' => $count,
        ];
    }

    /**
     * @param  array<string, mixed>  $signals
     */
    private function formatStyleSignals(array $signals): string
    {
        $parts = [];

        if (isset($signals['avg_sentence_length'])) {
            $parts[] = '~'.(int) $signals['avg_sentence_length'].' words/sentence';
        }
        if ($signals['uses_questions'] ?? false) {
            $parts[] = 'often uses questions';
        }
        if ($signals['uses_first_person'] ?? false) {
            $parts[] = 'first-person voice';
        }

        return implode('; ', $parts);
    }

    /**
     * @return array<string, mixed>
     */
    private function fallbackVariables(?string $targetAudience, ?string $contentPillar): array
    {
        return [
            'target_audience' => $targetAudience ?? 'LinkedIn professionals',
            'content_pillar' => $contentPillar ?? '',
            'compact_brand_memory' => '',
            'banned_phrases' => [],
            'preferred_ctas' => [],
            'preferred_hook_patterns' => [],
        ];
    }

    private function truncateSection(string $section, int $maxChars): string
    {
        if (strlen($section) <= $maxChars) {
            return $section;
        }

        return mb_substr($section, 0, $maxChars - 3).'...';
    }
}
