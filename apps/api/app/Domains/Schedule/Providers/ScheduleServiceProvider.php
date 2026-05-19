<?php

declare(strict_types=1);

namespace App\Domains\Schedule\Providers;

use App\Domains\Shared\Providers\DomainServiceProvider;

final class ScheduleServiceProvider extends DomainServiceProvider
{
    protected function domainName(): string
    {
        return 'Schedule';
    }
}
