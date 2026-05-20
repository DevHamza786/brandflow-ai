<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domains\Analytics\Services\EngagementNormalizationService;
use App\Domains\Analytics\Services\HookPerformanceScoringEngine;
use Tests\TestCase;

final class EngagementNormalizationServiceTest extends TestCase
{
    public function test_engagement_rate_increases_with_interactions(): void
    {
        $svc = new EngagementNormalizationService();

        $low = $svc->engagementRate(1000, 1, 0, 0, 0);
        $high = $svc->engagementRate(1000, 10, 5, 2, 1);

        $this->assertGreaterThan($low, $high);
        $this->assertGreaterThan(0.0, $svc->normalize($high));
        $this->assertLessThanOrEqual(1.0, $svc->normalize($high));
    }

    public function test_hook_performance_blends_lab_score_and_engagement(): void
    {
        $engine = new HookPerformanceScoringEngine();

        $score = $engine->score(['overall' => 80.0], 0.5);

        $this->assertNotNull($score);
        $this->assertGreaterThan(40.0, $score);
        $this->assertLessThanOrEqual(100.0, $score);
    }
}
