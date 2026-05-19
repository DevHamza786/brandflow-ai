<?php

declare(strict_types=1);

namespace App\Domains\Identity\Providers;

use App\Domains\Shared\Providers\DomainServiceProvider;

final class IdentityServiceProvider extends DomainServiceProvider
{
    protected function domainName(): string
    {
        return 'Identity';
    }
}
