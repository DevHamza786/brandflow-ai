<?php

declare(strict_types=1);

namespace App\Domains\AI\Support;

/**
 * Strips JSON Schema keywords unsupported by Gemini responseSchema.
 */
final class GeminiSchemaSanitizer
{
    /**
     * @param  array<string, mixed>  $schema
     * @return array<string, mixed>
     */
    public function sanitize(array $schema): array
    {
        unset($schema['additionalProperties'], $schema['$schema']);

        foreach ($schema as $key => $value) {
            if ($key === 'properties' && is_array($value)) {
                foreach ($value as $propKey => $propSchema) {
                    if (is_array($propSchema)) {
                        $schema['properties'][$propKey] = $this->sanitize($propSchema);
                    }
                }
            }

            if ($key === 'items' && is_array($value)) {
                $schema['items'] = $this->sanitize($value);
            }
        }

        return $schema;
    }
}
