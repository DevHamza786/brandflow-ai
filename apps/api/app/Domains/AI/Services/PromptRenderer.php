<?php

declare(strict_types=1);

namespace App\Domains\AI\Services;

use App\Domains\AI\Contracts\PromptRendererContract;
use Illuminate\Support\Facades\View;
use InvalidArgumentException;

/**
 * Renders Blade prompt templates under resources/prompts.
 */
final class PromptRenderer implements PromptRendererContract
{
    /**
     * @param  array<string, mixed>  $variables
     */
    public function render(string $view, array $variables): string
    {
        if (! View::exists($view)) {
            throw new InvalidArgumentException("Prompt view [{$view}] does not exist.");
        }

        return trim(View::make($view, $variables)->render());
    }
}
