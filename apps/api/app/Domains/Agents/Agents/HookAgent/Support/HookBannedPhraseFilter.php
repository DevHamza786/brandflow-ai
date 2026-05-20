<?php

declare(strict_types=1);

namespace App\Domains\Agents\Agents\HookAgent\Support;

use App\Domains\Agents\Agents\HookAgent\Data\HookVariant;
use Illuminate\Support\Facades\Log;

/**
 * Post-processes variants to enforce banned phrase constraints from brand profile.
 */
final class HookBannedPhraseFilter
{
    /**
     * @param  list<HookVariant>  $variants
     * @param  list<string>  $bannedPhrases
     * @return list<HookVariant>
     */
    public function filterVariants(array $variants, array $bannedPhrases): array
    {
        if ($bannedPhrases === []) {
            return $variants;
        }

        $filtered = [];

        foreach ($variants as $variant) {
            $sanitized = $this->sanitizeText($variant->text, $bannedPhrases);

            if (trim($sanitized) === '') {
                Log::warning('hook.variant.dropped_banned_phrases', [
                    'original' => $variant->text,
                ]);

                continue;
            }

            if ($sanitized !== $variant->text) {
                Log::info('hook.variant.banned_phrase_stripped', [
                    'original' => $variant->text,
                    'sanitized' => $sanitized,
                ]);
            }

            $filtered[] = new HookVariant(
                text: $sanitized,
                overall: $variant->overall,
                dimensions: $variant->dimensions,
                experimentVariant: $variant->experimentVariant,
            );
        }

        return $filtered;
    }

    /**
     * @param  list<string>  $bannedPhrases
     */
    private function sanitizeText(string $text, array $bannedPhrases): string
    {
        $result = $text;

        foreach ($bannedPhrases as $phrase) {
            $phrase = trim($phrase);
            if ($phrase === '') {
                continue;
            }

            // Case-insensitive removal (handles hyphenated phrases where \b is unreliable).
            $result = str_ireplace($phrase, '', $result);
        }

        return trim(preg_replace('/\s+/u', ' ', $result) ?? $result);
    }
}
