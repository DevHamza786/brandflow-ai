<?php

declare(strict_types=1);

namespace App\Domains\AI\Events;

use App\Domains\AI\Enums\GeneratedOutputType;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class GeneratedOutputFailed
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  array<string, mixed>  $error
     */
    public function __construct(
        public readonly string $workspaceId,
        public readonly string $generatedOutputId,
        public readonly GeneratedOutputType $type,
        public readonly ?string $agentRunId = null,
        public readonly ?string $workflowRunId = null,
        public readonly array $error = [],
    ) {
    }
}
