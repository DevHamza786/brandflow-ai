<?php

declare(strict_types=1);

namespace App\Domains\Workflows\Providers;

use App\Domains\Shared\Providers\DomainServiceProvider;

final class WorkflowsServiceProvider extends DomainServiceProvider
{
    protected function domainName(): string
    {
        return 'Workflows';
    }
}
