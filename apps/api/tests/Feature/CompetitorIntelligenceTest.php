<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Analytics\Services\EngagementTrackingService;
use App\Domains\Intelligence\Actions\CreateCompetitorAction;
use App\Domains\Intelligence\Actions\IngestCompetitorSnapshotAction;
use App\Domains\Intelligence\Data\CreateCompetitorDto;
use App\Domains\Intelligence\Data\IngestCompetitorSnapshotDto;
use App\Domains\Intelligence\Enums\CompetitorSnapshotSource;
use App\Domains\Intelligence\Models\CompetitorSnapshot;
use App\Domains\Intelligence\Services\CompetitorQueryService;
use App\Domains\Recommendations\Enums\RecommendationSource;
use App\Domains\Recommendations\Models\Recommendation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class CompetitorIntelligenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_snapshot_ingest_persists_analytics_and_hook_pattern_insights(): void
    {
        $workspaceId = $this->createWorkspace();
        $competitor = app(CreateCompetitorAction::class)->execute(new CreateCompetitorDto(
            workspaceId: $workspaceId,
            linkedinUrl: 'https://www.linkedin.com/in/competitor-test',
            name: 'Rival SaaS',
            labels: ['SaaS', 'founders'],
        ));

        $posts = [];
        for ($i = 0; $i < 4; $i++) {
            $posts[] = [
                'hook_text' => 'Why do SaaS founders ignore churn until renewal day?',
                'published_at' => now()->subDays($i)->toIso8601String(),
                'impressions' => 5000,
                'likes' => 400,
                'comments' => 40,
            ];
        }
        for ($i = 0; $i < 4; $i++) {
            $posts[] = [
                'hook_text' => 'Start posting more content this week.',
                'published_at' => now()->subDays($i + 5)->toIso8601String(),
                'impressions' => 5000,
                'likes' => 40,
                'comments' => 2,
            ];
        }

        $snapshot = app(IngestCompetitorSnapshotAction::class)->execute(new IngestCompetitorSnapshotDto(
            workspaceId: $workspaceId,
            competitorId: $competitor->id,
            payload: ['posts' => $posts],
            source: CompetitorSnapshotSource::ApiSimulate,
        ));

        $this->assertSame(8, $snapshot->postsCount);
        $this->assertGreaterThan(0, $snapshot->avgEngagementRate);
        $this->assertNotEmpty($snapshot->hookPatterns['styles'] ?? []);
        $insights = $snapshot->hookPatterns['insights'] ?? [];
        $this->assertNotEmpty($insights);
        $this->assertStringContainsString('outperform', strtolower((string) ($insights[0]['summary'] ?? '')));

        $this->assertDatabaseHas('competitor_snapshots', [
            'id' => $snapshot->id,
            'workspace_id' => $workspaceId,
            'competitor_id' => $competitor->id,
        ]);
    }

    public function test_competitor_recommendation_bridge_creates_evidence_backed_row(): void
    {
        $workspaceId = $this->createWorkspace();
        $competitor = app(CreateCompetitorAction::class)->execute(new CreateCompetitorDto(
            workspaceId: $workspaceId,
            linkedinUrl: 'https://www.linkedin.com/in/rival-2',
            name: 'Rival Two',
        ));

        $this->ingestQuestionVsCommandPayload($workspaceId, $competitor->id);

        $rec = Recommendation::query()
            ->where('workspace_id', $workspaceId)
            ->where('source', RecommendationSource::CompetitorIntelligence->value)
            ->first();

        $this->assertNotNull($rec);
        $this->assertStringContainsString('competitor', strtolower($rec->summary));
    }

    public function test_workspace_isolation(): void
    {
        $wsA = $this->createWorkspace();
        $wsB = $this->createWorkspace();

        $compA = app(CreateCompetitorAction::class)->execute(new CreateCompetitorDto(
            workspaceId: $wsA,
            linkedinUrl: 'https://www.linkedin.com/in/a-only',
            name: 'A',
        ));

        app(IngestCompetitorSnapshotAction::class)->execute(new IngestCompetitorSnapshotDto(
            workspaceId: $wsA,
            competitorId: $compA->id,
            payload: ['posts' => [['hook_text' => 'Why do founders fail?', 'likes' => 10, 'impressions' => 100]]],
        ));

        $report = app(CompetitorQueryService::class)->intelligenceReport($wsB, $compA->id);
        $this->assertNull($report);

        $this->assertSame(0, CompetitorSnapshot::query()->where('workspace_id', $wsB)->count());
    }

    public function test_benchmark_against_workspace_snapshots(): void
    {
        $workspaceId = $this->createWorkspace();
        app(EngagementTrackingService::class)->recordPostEngagement(
            workspaceId: $workspaceId,
            entityType: 'scheduled_post',
            entityId: Str::uuid()->toString(),
            impressions: 10000,
            likes: 20,
            comments: 2,
        );

        $competitor = app(CreateCompetitorAction::class)->execute(new CreateCompetitorDto(
            workspaceId: $workspaceId,
            linkedinUrl: 'https://www.linkedin.com/in/benchmark-rival',
            name: 'Benchmark Rival',
        ));

        $snapshot = $this->ingestQuestionVsCommandPayload($workspaceId, $competitor->id);
        $benchmark = $snapshot->trendSummary['benchmark'] ?? [];
        $this->assertArrayHasKey('workspace_avg_engagement_rate', $benchmark);
        $this->assertArrayHasKey('competitor_avg_engagement_rate', $benchmark);
    }

    public function test_competitors_api_ingest_flow(): void
    {
        $workspaceId = $this->createWorkspace();

        $create = $this->postJson('/api/v1/competitors', [
            'linkedin_url' => 'https://www.linkedin.com/in/api-comp',
            'name' => 'API Competitor',
            'labels' => ['niche'],
        ], ['X-Workspace-Id' => $workspaceId]);

        $create->assertStatus(201);
        $competitorId = $create->json('data.id');

        $ingest = $this->postJson("/api/v1/competitors/{$competitorId}/snapshots", [
            'payload' => [
                'posts' => [
                    [
                        'hook_text' => 'What is the #1 mistake SaaS CEOs make?',
                        'published_at' => now()->toIso8601String(),
                        'impressions' => 2000,
                        'likes' => 300,
                        'comments' => 30,
                    ],
                    [
                        'hook_text' => 'What is the hidden cost of churn?',
                        'published_at' => now()->subDay()->toIso8601String(),
                        'impressions' => 2000,
                        'likes' => 280,
                        'comments' => 25,
                    ],
                    [
                        'hook_text' => 'Build your brand in 30 days.',
                        'published_at' => now()->subDays(2)->toIso8601String(),
                        'impressions' => 2000,
                        'likes' => 30,
                        'comments' => 1,
                    ],
                ],
            ],
        ], ['X-Workspace-Id' => $workspaceId]);

        $ingest->assertStatus(202)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.posts_count', 3);
    }

    private function ingestQuestionVsCommandPayload(string $workspaceId, string $competitorId): \App\Domains\Intelligence\Data\CompetitorSnapshotDto
    {
        $posts = [];
        for ($i = 0; $i < 4; $i++) {
            $posts[] = [
                'hook_text' => 'Why do SaaS founders miss churn signals?',
                'published_at' => now()->subDays($i)->toIso8601String(),
                'impressions' => 8000,
                'likes' => 500,
                'comments' => 50,
            ];
        }
        for ($i = 0; $i < 4; $i++) {
            $posts[] = [
                'hook_text' => 'Start building your LinkedIn presence today.',
                'published_at' => now()->subDays($i + 4)->toIso8601String(),
                'impressions' => 8000,
                'likes' => 35,
                'comments' => 2,
            ];
        }

        return app(IngestCompetitorSnapshotAction::class)->execute(new IngestCompetitorSnapshotDto(
            workspaceId: $workspaceId,
            competitorId: $competitorId,
            payload: ['posts' => $posts],
            source: CompetitorSnapshotSource::ApiSimulate,
        ));
    }

    private function createWorkspace(): string
    {
        $workspaceId = Str::uuid()->toString();
        \Illuminate\Support\Facades\DB::table('workspaces')->insert([
            'id' => $workspaceId,
            'name' => 'Intel WS',
            'slug' => 'intel-'.substr($workspaceId, 0, 6),
            'plan_id' => null,
            'settings' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $workspaceId;
    }
}
