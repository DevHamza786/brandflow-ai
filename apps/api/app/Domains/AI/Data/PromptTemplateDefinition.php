<?php

declare(strict_types=1);

namespace App\Domains\AI\Data;

use App\Domains\Shared\Data\DataTransferObject;

/**
 * Resolved prompt template metadata.
 */
final class PromptTemplateDefinition extends DataTransferObject
{
    /**
     * @param  list<string>  $requiredVariables
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        public readonly string $slug,
        public readonly string $version,
        public readonly string $view,
        public readonly array $requiredVariables = [],
        public readonly ?string $defaultProvider = null,
        public readonly ?string $defaultModel = null,
        public readonly array $config = [],
    ) {
    }
}
