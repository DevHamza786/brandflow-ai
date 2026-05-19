<?php

declare(strict_types=1);

namespace App\Domains\Integrations\Providers;

use App\Domains\Shared\Providers\DomainServiceProvider;

final class IntegrationsServiceProvider extends DomainServiceProvider
{
    protected function domainName(): string
    {
        return 'Integrations';
    }
}
