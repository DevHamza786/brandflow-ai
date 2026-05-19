<?php

declare(strict_types=1);

namespace App\Domains\Analytics\Providers;

use App\Domains\Shared\Providers\DomainServiceProvider;

final class AnalyticsServiceProvider extends DomainServiceProvider
{
    protected function domainName(): string
    {
        return 'Analytics';
    }
}
