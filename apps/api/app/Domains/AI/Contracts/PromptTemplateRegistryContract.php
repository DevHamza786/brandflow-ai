<?php

declare(strict_types=1);

namespace App\Domains\AI\Contracts;

use App\Domains\AI\Data\PromptTemplateDefinition;

interface PromptTemplateRegistryContract
{
    public function resolve(string $slug, ?string $version = null): PromptTemplateDefinition;

    /**
     * @param  array<string, mixed>  $variables
     */
    public function render(string $slug, array $variables, ?string $version = null): string;
}
