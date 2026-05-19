<?php

declare(strict_types=1);

namespace App\Domains\Brand\Providers;

use App\Domains\Shared\Providers\DomainServiceProvider;

final class BrandServiceProvider extends DomainServiceProvider
{
    protected function domainName(): string
    {
        return 'Brand';
    }
}
