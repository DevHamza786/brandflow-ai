<?php

declare(strict_types=1);

namespace App\Domains\Content\Providers;

use App\Domains\Shared\Providers\DomainServiceProvider;

final class ContentServiceProvider extends DomainServiceProvider
{
    protected function domainName(): string
    {
        return 'Content';
    }
}
