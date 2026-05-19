<?php

declare(strict_types=1);

namespace App\Domains\AI\Contracts;

/**
 * Resolves and renders versioned prompt templates.
 */
interface PromptTemplateRegistryContract
{
    /**
     * @param  array<string, mixed>  $variables
     */
    public function render(string $slug, array $variables, ?string $version = null): string;
}
