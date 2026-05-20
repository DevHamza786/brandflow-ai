<?php

declare(strict_types=1);

namespace App\Domains\AI\Events;

use App\Domains\AI\Enums\GeneratedOutputType;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class GeneratedOutputCompleted
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  array<string, mixed>  $payload  Analytics-ready properties
     */
    public function __construct(
        public readonly string $workspaceId,
        public readonly string $generatedOutputId,
        public readonly GeneratedOutputType $type,
        public readonly ?string $agentRunId = null,
        public readonly ?string $workflowRunId = null,
        public readonly ?string $contentVersionId = null,
        public readonly array $payload = [],
    ) {
    }
}
