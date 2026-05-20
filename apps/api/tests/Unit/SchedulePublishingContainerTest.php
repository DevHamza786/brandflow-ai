<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domains\Schedule\Contracts\ScheduledPostRepositoryContract;
use App\Domains\Schedule\Services\LinkedInPublishingService;
use Tests\TestCase;

final class SchedulePublishingContainerTest extends TestCase
{
    public function test_scheduled_post_repository_contract_is_bound(): void
    {
        $this->assertTrue(
            $this->app->bound(ScheduledPostRepositoryContract::class),
            'ScheduledPostRepositoryContract must be bound so queue workers can resolve LinkedInPublishingService.',
        );
    }

    public function test_linkedin_publishing_service_resolves_from_container(): void
    {
        $service = $this->app->make(LinkedInPublishingService::class);

        $this->assertInstanceOf(LinkedInPublishingService::class, $service);
    }
}
