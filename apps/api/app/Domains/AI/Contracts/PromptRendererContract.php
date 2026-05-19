<?php

declare(strict_types=1);

namespace App\Domains\AI\Contracts;

interface PromptRendererContract
{
    /**
     * @param  array<string, mixed>  $variables
     */
    public function render(string $view, array $variables): string;
}
