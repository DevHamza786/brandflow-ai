<?php

declare(strict_types=1);

namespace App\Domains\Recommendations\Support;

/**
 * Heuristic hook-style tags for correlation (replaceable by embeddings later).
 */
final class HookStyleClassifier
{
    public const STYLE_QUESTION = 'question';
    public const STYLE_STATISTIC = 'statistic';
    public const STYLE_STORY = 'story';
    public const STYLE_CONTRARIAN = 'contrarian';
    public const STYLE_LISTICLE = 'listicle';
    public const STYLE_COMMAND = 'command';
    public const STYLE_UNKNOWN = 'unknown';

    public function classify(?string $hookText): string
    {
        $text = trim((string) $hookText);
        if ($text === '') {
            return self::STYLE_UNKNOWN;
        }

        $lower = mb_strtolower($text);

        if (preg_match('/\?\s*$/', $text) || preg_match('/^(what|why|how|when|who|which|are you|do you)\b/i', $lower)) {
            return self::STYLE_QUESTION;
        }

        if (preg_match('/\d+\s*%|\b\d+x\b|\b\d+\s+(ways|reasons|lessons|mistakes)\b/i', $lower)) {
            return self::STYLE_STATISTIC;
        }

        if (preg_match('/\b\d+\s+(ways|tips|steps|things)\b/i', $lower)) {
            return self::STYLE_LISTICLE;
        }

        if (preg_match('/^(i |when i |last year|yesterday|my first)/i', $lower)) {
            return self::STYLE_STORY;
        }

        if (preg_match('/\b(most people|stop |don\'t |never |unpopular|myth)\b/i', $lower)) {
            return self::STYLE_CONTRARIAN;
        }

        if (preg_match('/^(stop|start|build|ship|fix|learn|avoid|use)\b/i', $lower) && mb_strlen($text) < 120) {
            return self::STYLE_COMMAND;
        }

        return self::STYLE_UNKNOWN;
    }

    public function label(string $style): string
    {
        return match ($style) {
            self::STYLE_QUESTION => 'question-based',
            self::STYLE_STATISTIC => 'statistic-led',
            self::STYLE_STORY => 'story-led',
            self::STYLE_CONTRARIAN => 'contrarian',
            self::STYLE_LISTICLE => 'listicle',
            self::STYLE_COMMAND => 'command-style',
            default => 'general',
        };
    }
}
