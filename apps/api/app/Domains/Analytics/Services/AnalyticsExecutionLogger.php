<?php

declare(strict_types=1);

namespace App\Domains\Analytics\Services;

use Illuminate\Support\Facades\Log;

final class AnalyticsExecutionLogger
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function info(string $event, array $context = []): void
    {
        Log::info('analytics.'.$event, array_merge([
            'ts' => now()->toIso8601String(),
        ], $context));
    }
}
