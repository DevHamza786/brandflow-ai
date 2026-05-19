<?php

declare(strict_types=1);

namespace App\Domains\Content\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Emitted when HookAgent completes — ready for analytics ingestion listeners.
 */
final class HookScored
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  array<string, mixed>  $payload  Analytics-ready properties
     */
    public function __construct(
        public readonly string $workspaceId,
        public readonly string $contentVersionId,
        public readonly string $agentRunId,
        public readonly string $hookScoreId,
        public readonly array $payload,
    ) {
    }
}
