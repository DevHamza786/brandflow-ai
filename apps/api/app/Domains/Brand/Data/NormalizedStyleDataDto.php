<?php

declare(strict_types=1);

namespace App\Domains\Brand\Data;

use App\Domains\Shared\Data\DataTransferObject;

/**
 * Heuristic / extracted style signals from a writing sample (embedding-pipeline input).
 */
final class NormalizedStyleDataDto extends DataTransferObject
{
    /**
     * @param  list<string>  $signaturePhrases
     * @param  list<string>  $vocabularyNotes
     */
    public function __construct(
        public readonly float $avgSentenceLength = 0.0,
        public readonly float $avgWordLength = 0.0,
        public readonly bool $usesFirstPerson = false,
        public readonly bool $usesQuestions = false,
        public readonly int $exclamationCount = 0,
        public readonly int $emojiCount = 0,
        public readonly array $signaturePhrases = [],
        public readonly array $vocabularyNotes = [],
        public readonly string $extractedAt = '',
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): static
    {
        return new self(
            avgSentenceLength: (float) ($data['avg_sentence_length'] ?? $data['avgSentenceLength'] ?? 0),
            avgWordLength: (float) ($data['avg_word_length'] ?? $data['avgWordLength'] ?? 0),
            usesFirstPerson: (bool) ($data['uses_first_person'] ?? $data['usesFirstPerson'] ?? false),
            usesQuestions: (bool) ($data['uses_questions'] ?? $data['usesQuestions'] ?? false),
            exclamationCount: (int) ($data['exclamation_count'] ?? $data['exclamationCount'] ?? 0),
            emojiCount: (int) ($data['emoji_count'] ?? $data['emojiCount'] ?? 0),
            signaturePhrases: self::stringList($data['signature_phrases'] ?? []),
            vocabularyNotes: self::stringList($data['vocabulary_notes'] ?? []),
            extractedAt: (string) ($data['extracted_at'] ?? $data['extractedAt'] ?? ''),
        );
    }

    /**
     * @param  mixed  $value
     * @return list<string>
     */
    private static function stringList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn ($v) => is_string($v) ? trim($v) : '',
            $value
        )));
    }
}
