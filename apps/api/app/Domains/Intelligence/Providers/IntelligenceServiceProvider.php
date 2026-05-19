<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Providers;

use App\Domains\Shared\Providers\DomainServiceProvider;

final class IntelligenceServiceProvider extends DomainServiceProvider
{
    protected function domainName(): string
    {
        return 'Intelligence';
    }
}
