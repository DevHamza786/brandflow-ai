<?php

declare(strict_types=1);

namespace App\Domains\Integrations\Events;

use App\Domains\Integrations\Data\LinkedInIntegrationDto;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class LinkedInIntegrationRefreshed
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly LinkedInIntegrationDto $integration,
        public readonly string $traceId,
    ) {
    }
}
