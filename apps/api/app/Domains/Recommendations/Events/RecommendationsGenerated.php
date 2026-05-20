<?php

declare(strict_types=1);

namespace App\Domains\Recommendations\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class RecommendationsGenerated
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  array<string, int>  $countsByType
     */
    public function __construct(
        public readonly string $workspaceId,
        public readonly int $generatedCount,
        public readonly array $countsByType,
    ) {
    }
}
