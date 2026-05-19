<?php

declare(strict_types=1);

namespace App\Domains\AI\Data;

use App\Domains\Shared\Data\DataTransferObject;

/**
 * Structured JSON output configuration for chat completions.
 */
final class StructuredOutputConfig extends DataTransferObject
{
    /**
     * @param  array<string, mixed>|null  $schema  JSON Schema when type is json_schema
     */
    public function __construct(
        public readonly string $type = 'json_object',
        public readonly ?string $schemaName = null,
        public readonly ?array $schema = null,
        public readonly bool $strict = true,
    ) {
    }

    public static function jsonObject(): self
    {
        return new self(type: 'json_object');
    }

    /**
     * @param  array<string, mixed>  $schema
     */
    public static function jsonSchema(string $name, array $schema, bool $strict = true): self
    {
        return new self(
            type: 'json_schema',
            schemaName: $name,
            schema: $schema,
            strict: $strict,
        );
    }
}
