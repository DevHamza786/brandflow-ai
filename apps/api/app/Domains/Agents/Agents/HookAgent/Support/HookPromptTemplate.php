<?php

declare(strict_types=1);

namespace App\Domains\Agents\Agents\HookAgent\Support;

use App\Domains\AI\Data\StructuredOutputConfig;

/**
 * Prompt slugs, versions, and JSON schemas for Hook Lab.
 */
final class HookPromptTemplate
{
    public const SCORER_SLUG = 'hook.scorer';

    public const GENERATOR_SLUG = 'hook.variant_generator';

    public const SCHEMA_SCORER = 'hook_score_v1';

    public const SCHEMA_GENERATOR = 'hook_variants_v1';

    public static function scorerStructuredOutput(): StructuredOutputConfig
    {
        return StructuredOutputConfig::jsonSchema(self::SCHEMA_SCORER, self::scorerSchema());
    }

    public static function generatorStructuredOutput(): StructuredOutputConfig
    {
        return StructuredOutputConfig::jsonSchema(self::SCHEMA_GENERATOR, self::generatorSchema());
    }

    /**
     * @return array<string, mixed>
     */
    public static function scorerSchema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'required' => ['overall', 'dimensions', 'suggestions'],
            'properties' => [
                'overall' => ['type' => 'number', 'minimum' => 0, 'maximum' => 100],
                'dimensions' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'required' => ['curiosity_gap', 'specificity', 'clarity', 'audience_fit'],
                    'properties' => [
                        'curiosity_gap' => ['type' => 'number', 'minimum' => 0, 'maximum' => 100],
                        'specificity' => ['type' => 'number', 'minimum' => 0, 'maximum' => 100],
                        'clarity' => ['type' => 'number', 'minimum' => 0, 'maximum' => 100],
                        'audience_fit' => ['type' => 'number', 'minimum' => 0, 'maximum' => 100],
                    ],
                ],
                'suggestions' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function generatorSchema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'required' => ['variants'],
            'properties' => [
                'variants' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'required' => ['text', 'overall', 'dimensions'],
                        'properties' => [
                            'text' => ['type' => 'string'],
                            'overall' => ['type' => 'number', 'minimum' => 0, 'maximum' => 100],
                            'dimensions' => [
                                'type' => 'object',
                                'additionalProperties' => false,
                                'required' => ['curiosity_gap', 'specificity', 'clarity', 'audience_fit'],
                                'properties' => [
                                    'curiosity_gap' => ['type' => 'number'],
                                    'specificity' => ['type' => 'number'],
                                    'clarity' => ['type' => 'number'],
                                    'audience_fit' => ['type' => 'number'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
