<?php

declare(strict_types=1);

namespace App\Domains\Agents\Agents\HookAgent\Support;

use App\Domains\Agents\Agents\HookAgent\Data\HookCollection;
use App\Domains\Agents\Agents\HookAgent\Data\HookResult;
use App\Domains\Agents\Agents\HookAgent\Data\HookVariant;
use App\Domains\Agents\Agents\HookAgent\Exceptions\HookValidationException;

/**
 * Validates Hook Lab DTOs after parsing (defense in depth beyond JSON schema).
 */
final class HookResponseValidator
{
    public function validateHookText(string $hookText): void
    {
        if (trim($hookText) === '') {
            throw new HookValidationException('Content has no opening lines to score as a hook.');
        }
    }

    public function validateScorerResult(HookResult $result): void
    {
        $this->validateScoreRange($result->overall, 'overall');

        foreach (['curiosity_gap', 'specificity', 'clarity', 'audience_fit'] as $field) {
            $value = $result->dimensions->toArray()[$field] ?? 0;
            $this->validateScoreRange((float) $value, $field);
        }
    }

    /**
     * @param  list<HookVariant>  $variants
     */
    public function validateVariants(array $variants, int $maxVariants): void
    {
        if (count($variants) > $maxVariants) {
            throw new HookValidationException(
                'Variant count exceeds configured maximum.',
                ['count' => count($variants), 'max' => $maxVariants]
            );
        }

        foreach ($variants as $index => $variant) {
            if (trim($variant->text) === '') {
                throw new HookValidationException("Variant [{$index}] has empty text.");
            }

            $this->validateScoreRange($variant->overall, "variants.{$index}.overall");
        }
    }

    public function validateCollection(HookCollection $collection, int $maxVariants): void
    {
        $this->validateScorerResult($collection->primary);
        $this->validateVariants($collection->variants, $maxVariants);
    }

    private function validateScoreRange(float $score, string $field): void
    {
        if ($score < 0 || $score > 100) {
            throw new HookValidationException(
                "Score [{$field}] must be between 0 and 100.",
                ['field' => $field, 'value' => $score]
            );
        }
    }
}
