<?php

declare(strict_types=1);

namespace App\Domains\Brand\Services;

use App\Domains\Brand\Contracts\WritingStyleExtractionServiceContract;
use App\Domains\Brand\Data\NormalizedStyleDataDto;
use Illuminate\Support\Str;

/**
 * Rule-based style extraction — no LLM provider coupling; upgrade path for ML extraction.
 */
final class WritingStyleExtractionService implements WritingStyleExtractionServiceContract
{
    public function extract(string $content): NormalizedStyleDataDto
    {
        $trimmed = trim($content);
        if ($trimmed === '') {
            return new NormalizedStyleDataDto(extractedAt: now()->toIso8601String());
        }

        $sentences = preg_split('/[.!?]+/u', $trimmed, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $sentenceCount = max(1, count($sentences));
        $words = preg_split('/\s+/u', $trimmed, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $wordCount = max(1, count($words));

        $charCount = mb_strlen($trimmed);
        $avgSentenceLength = $wordCount / $sentenceCount;
        $avgWordLength = $charCount / max(1, $wordCount);

        $lower = mb_strtolower($trimmed);

        return new NormalizedStyleDataDto(
            avgSentenceLength: round($avgSentenceLength, 2),
            avgWordLength: round($avgWordLength, 2),
            usesFirstPerson: (bool) preg_match('/\b(i|i\'m|i’ve|my|we|our)\b/i', $trimmed),
            usesQuestions: str_contains($trimmed, '?'),
            exclamationCount: substr_count($trimmed, '!'),
            emojiCount: $this->countEmojis($trimmed),
            signaturePhrases: $this->extractSignaturePhrases($trimmed),
            vocabularyNotes: $this->vocabularyNotes($lower),
            extractedAt: now()->toIso8601String(),
        );
    }

    private function countEmojis(string $text): int
    {
        return preg_match_all('/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}]/u', $text) ?: 0;
    }

    /**
     * @return list<string>
     */
    private function extractSignaturePhrases(string $text): array
    {
        $lines = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $text) ?: []));
        $phrases = [];

        foreach (array_slice($lines, 0, 3) as $line) {
            if (mb_strlen($line) >= 12 && mb_strlen($line) <= 120) {
                $phrases[] = $line;
            }
        }

        return array_values(array_unique($phrases));
    }

    /**
     * @return list<string>
     */
    private function vocabularyNotes(string $lower): array
    {
        $notes = [];

        if (Str::contains($lower, ['b2b', 'saas', 'founder', 'revenue'])) {
            $notes[] = 'business_growth_lexicon';
        }
        if (Str::contains($lower, ['learn', 'framework', 'playbook', 'guide'])) {
            $notes[] = 'educational_framing';
        }
        if (preg_match('/\d+%|\d+x|\$\d+/i', $lower)) {
            $notes[] = 'uses_quantified_claims';
        }

        return $notes;
    }
}
