<?php

declare(strict_types=1);

/**
 * Bounded context registry for the modular monolith.
 */
return [

    'providers' => [
        \App\Domains\AI\Providers\AIServiceProvider::class,
        \App\Domains\Agents\Providers\AgentsServiceProvider::class,
        \App\Domains\Analytics\Providers\AnalyticsServiceProvider::class,
        \App\Domains\Brand\Providers\BrandServiceProvider::class,
        \App\Domains\Content\Providers\ContentServiceProvider::class,
        \App\Domains\Identity\Providers\IdentityServiceProvider::class,
        \App\Domains\Integrations\Providers\IntegrationsServiceProvider::class,
        \App\Domains\Intelligence\Providers\IntelligenceServiceProvider::class,
        \App\Domains\Schedule\Providers\ScheduleServiceProvider::class,
        \App\Domains\Workflows\Providers\WorkflowsServiceProvider::class,
    ],

];
