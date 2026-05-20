<?php

declare(strict_types=1);

namespace App\Domains\Integrations\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class LinkedInIntegrationRefreshFailed
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly string $workspaceId,
        public readonly string $integrationId,
        public readonly string $message,
        public readonly string $traceId,
    ) {
    }
}
