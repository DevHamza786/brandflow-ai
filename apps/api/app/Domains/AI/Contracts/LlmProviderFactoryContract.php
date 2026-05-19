<?php

declare(strict_types=1);

namespace App\Domains\AI\Contracts;

use App\Domains\AI\Enums\LlmProvider;

interface LlmProviderFactoryContract
{
    public function make(LlmProvider|string $provider): LlmProviderAdapter;

    public function makeDefault(): LlmProviderAdapter;

    public function makeFallback(): LlmProviderAdapter;

    /**
     * @return list<LlmProviderAdapter>
     */
    public function configured(): array;
}
