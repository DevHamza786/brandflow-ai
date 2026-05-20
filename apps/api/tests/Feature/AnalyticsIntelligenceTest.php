<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Analytics\Models\AnalyticsEvent;
use App\Domains\Analytics\Models\PostPerformanceSnapshot;
use App\Domains\Analytics\Services\AnalyticsOrchestrationService;
use App\Domains\Analytics\Services\EngagementTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class AnalyticsIntelligenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_engagement_tracking_persists_snapshot_and_metrics(): void
    {
        $workspaceId = Str::uuid()->toString();
        $entityId = Str::uuid()->toString();

        \Illuminate\Support\Facades\DB::table('workspaces')->insert([
            'id' => $workspaceId,
            'name' => 'Analytics WS',
            'slug' => 'an-'.substr($workspaceId, 0, 6),
            'plan_id' => null,
            'settings' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        app(EngagementTrackingService::class)->recordPostEngagement(
            workspaceId: $workspaceId,
            entityType: 'scheduled_post',
            entityId: $entityId,
            impressions: 1000,
            likes: 50,
            comments: 10,
            reposts: 5,
            saves: 3,
            hookPerformance: [
                'text' => 'Stop scrolling — here is the insight.',
                'overall' => 72.5,
            ],
        );

        $snapshot = PostPerformanceSnapshot::query()
            ->where('workspace_id', $workspaceId)
            ->where('entity_id', $entityId)
            ->first();

        $this->assertNotNull($snapshot);
        $this->assertSame(1000, (int) $snapshot->impressions);
        $this->assertSame(50, (int) $snapshot->likes);
        $this->assertGreaterThan(0.0, (float) $snapshot->engagement_rate);
        $this->assertGreaterThan(0.0, (float) $snapshot->normalized_engagement);

        $this->assertDatabaseHas('engagement_metrics', [
            'workspace_id' => $workspaceId,
            'measurable_id' => $entityId,
            'metric_type' => 'likes',
        ]);

        $this->assertDatabaseHas('analytics_events', [
            'workspace_id' => $workspaceId,
            'event_type' => 'post.performance_observed',
            'entity_id' => $entityId,
        ]);
    }

    public function test_hook_scored_ingestion_is_idempotent(): void
    {
        $workspaceId = Str::uuid()->toString();
        $hookScoreId = Str::uuid()->toString();

        \Illuminate\Support\Facades\DB::table('workspaces')->insert([
            'id' => $workspaceId,
            'name' => 'Hook WS',
            'slug' => 'hk-'.substr($workspaceId, 0, 6),
            'plan_id' => null,
            'settings' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $orch = app(AnalyticsOrchestrationService::class);

        $orch->recordHookScored(
            workspaceId: $workspaceId,
            contentVersionId: Str::uuid()->toString(),
            agentRunId: Str::uuid()->toString(),
            hookScoreId: $hookScoreId,
            hookPayload: ['variants' => []],
        );

        $orch->recordHookScored(
            workspaceId: $workspaceId,
            contentVersionId: Str::uuid()->toString(),
            agentRunId: Str::uuid()->toString(),
            hookScoreId: $hookScoreId,
            hookPayload: ['variants' => []],
        );

        $count = AnalyticsEvent::query()
            ->where('workspace_id', $workspaceId)
            ->where('event_type', 'hook.scored')
            ->count();

        $this->assertSame(1, $count);
    }

    public function test_query_layer_returns_top_hooks(): void
    {
        $workspaceId = Str::uuid()->toString();

        \Illuminate\Support\Facades\DB::table('workspaces')->insert([
            'id' => $workspaceId,
            'name' => 'Top WS',
            'slug' => 'tp-'.substr($workspaceId, 0, 6),
            'plan_id' => null,
            'settings' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $tracking = app(EngagementTrackingService::class);

        $tracking->recordPostEngagement(
            workspaceId: $workspaceId,
            entityType: 'hook_variant',
            entityId: Str::uuid()->toString(),
            impressions: 500,
            likes: 5,
            comments: 1,
        );

        $tracking->recordPostEngagement(
            workspaceId: $workspaceId,
            entityType: 'hook_variant',
            entityId: Str::uuid()->toString(),
            impressions: 500,
            likes: 80,
            comments: 20,
        );

        $top = app(AnalyticsOrchestrationService::class)->query()->topPerformingHooks($workspaceId, 1);

        $this->assertCount(1, $top);
        $this->assertNotNull($top[0]['normalized']);
        $this->assertGreaterThan(0.05, (float) $top[0]['normalized']);
    }

    public function test_analytics_dashboard_endpoint_returns_read_model(): void
    {
        $workspaceId = Str::uuid()->toString();

        \Illuminate\Support\Facades\DB::table('workspaces')->insert([
            'id' => $workspaceId,
            'name' => 'Dash WS',
            'slug' => 'dash-'.substr($workspaceId, 0, 6),
            'plan_id' => null,
            'settings' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        app(EngagementTrackingService::class)->recordPostEngagement(
            workspaceId: $workspaceId,
            entityType: 'scheduled_post',
            entityId: Str::uuid()->toString(),
            impressions: 2000,
            likes: 120,
            comments: 15,
            hookPerformance: ['text' => 'Bold hook line', 'overall' => 80.0],
        );

        $response = $this->getJson('/api/v1/analytics/dashboard?preset=30d', [
            'X-Workspace-Id' => $workspaceId,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.kpis.impressions', 2000)
            ->assertJsonPath('data.kpis.posts_observed', 1)
            ->assertJsonStructure([
                'data' => [
                    'range',
                    'kpis',
                    'engagement_series',
                    'score_trend',
                    'posting_frequency',
                    'posting_time',
                    'top_hooks',
                    'audience_overview',
                    'comparison',
                ],
            ]);
    }
}
