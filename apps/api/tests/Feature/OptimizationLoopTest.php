<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Analytics\Data\CreatePostPerformanceSnapshotDto;
use App\Domains\Analytics\Services\PerformanceAggregationService;
use App\Domains\Brand\Models\BrandProfile;
use App\Domains\Optimization\Actions\RunOptimizationCycleAction;
use App\Domains\Optimization\Models\OptimizationLoop;
use App\Domains\Optimization\Models\OptimizationSnapshot;
use App\Domains\Optimization\Services\OptimizationQueryService;
use App\Domains\Recommendations\Enums\RecommendationSource;
use App\Domains\Recommendations\Models\Recommendation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class OptimizationLoopTest extends TestCase
{
    use RefreshDatabase;

    public function test_hook_period_comparison_persists_optimization_intelligence(): void
    {
        $workspaceId = $this->createWorkspace();
        $this->createBrandProfile($workspaceId, ['SaaS founders'], ['Ask a bold question']);

        $aggregation = app(PerformanceAggregationService::class);

        for ($i = 0; $i < 4; $i++) {
            $aggregation->persistSnapshot(new CreatePostPerformanceSnapshotDto(
                workspaceId: $workspaceId,
                entityType: 'scheduled_post',
                entityId: Str::uuid()->toString(),
                observedAt: now()->subDays(5 + $i),
                postedAt: now()->subDays(5 + $i)->setHour(9),
                impressions: 4000,
                likes: 320,
                comments: 35,
                hookPerformance: [
                    'text' => 'Why do SaaS founders ignore churn until renewal day?',
                    'overall' => 82.0,
                    'dimensions' => [
                        'curiosity_gap' => 88,
                        'specificity' => 80,
                        'clarity' => 85,
                        'audience_fit' => 90,
                    ],
                ],
            ));
        }

        for ($i = 0; $i < 4; $i++) {
            $aggregation->persistSnapshot(new CreatePostPerformanceSnapshotDto(
                workspaceId: $workspaceId,
                entityType: 'scheduled_post',
                entityId: Str::uuid()->toString(),
                observedAt: now()->subDays(40 + $i),
                postedAt: now()->subDays(40 + $i)->setHour(3),
                impressions: 4000,
                likes: 25,
                comments: 2,
                hookPerformance: [
                    'text' => 'Start posting more content this week.',
                    'overall' => 70.0,
                    'dimensions' => [
                        'curiosity_gap' => 55,
                        'specificity' => 50,
                        'clarity' => 65,
                        'audience_fit' => 52,
                    ],
                ],
            ));
        }

        $result = app(RunOptimizationCycleAction::class)->execute(
            $workspaceId,
            lookbackDays: 30,
            comparisonDays: 30,
        );

        $this->assertGreaterThan(0, $result->snapshotsCreated);
        $this->assertSame(1, $result->cycleNumber);

        $hookSnapshot = OptimizationSnapshot::query()
            ->where('workspace_id', $workspaceId)
            ->where('engine', 'hook_structure')
            ->first();

        $this->assertNotNull($hookSnapshot);
        $this->assertStringContainsString('improved normalized engagement', $hookSnapshot->summary);
        $this->assertStringContainsString('question', strtolower($hookSnapshot->summary));
        $this->assertIsArray($hookSnapshot->delta_metrics);
        $this->assertArrayHasKey('uplift_pct', $hookSnapshot->delta_metrics);
        $this->assertGreaterThan(10, (float) ($hookSnapshot->delta_metrics['uplift_pct'] ?? 0));

        $loop = OptimizationLoop::query()->where('workspace_id', $workspaceId)->first();
        $this->assertNotNull($loop);
        $this->assertSame(1, $loop->current_cycle);
    }

    public function test_optimization_cycle_syncs_recommendation_with_optimization_source(): void
    {
        $workspaceId = $this->createWorkspace();
        $this->createBrandProfile($workspaceId, ['operators'], ['Book a call']);

        $aggregation = app(PerformanceAggregationService::class);
        for ($i = 0; $i < 4; $i++) {
            $aggregation->persistSnapshot(new CreatePostPerformanceSnapshotDto(
                workspaceId: $workspaceId,
                entityType: 'scheduled_post',
                entityId: Str::uuid()->toString(),
                observedAt: now()->subDays(3 + $i),
                postedAt: now()->subDays(3 + $i)->setHour(14),
                impressions: 3000,
                likes: 250,
                comments: 20,
                hookPerformance: [
                    'text' => 'What is the one metric founders ignore until it is too late?',
                    'overall' => 85.0,
                    'dimensions' => [
                        'curiosity_gap' => 90,
                        'specificity' => 82,
                        'clarity' => 80,
                        'audience_fit' => 88,
                    ],
                ],
            ));
        }
        for ($i = 0; $i < 4; $i++) {
            $aggregation->persistSnapshot(new CreatePostPerformanceSnapshotDto(
                workspaceId: $workspaceId,
                entityType: 'scheduled_post',
                entityId: Str::uuid()->toString(),
                observedAt: now()->subDays(45 + $i),
                impressions: 3000,
                likes: 15,
                comments: 1,
                hookPerformance: ['text' => 'Ship faster this quarter.', 'overall' => 72.0],
            ));
        }

        app(RunOptimizationCycleAction::class)->execute($workspaceId, 30, 30);

        $rec = Recommendation::query()
            ->where('workspace_id', $workspaceId)
            ->where('source', RecommendationSource::OptimizationLoop->value)
            ->where('status', 'active')
            ->first();

        $this->assertNotNull($rec);
        $this->assertIsArray($rec->evidence);
    }

    public function test_workspace_isolation_on_optimization_queries(): void
    {
        $wsA = $this->createWorkspace();
        $wsB = $this->createWorkspace();

        app(RunOptimizationCycleAction::class)->execute($wsA, 30, 30);

        $query = app(OptimizationQueryService::class);
        $loopsB = $query->listLoops($wsB);

        $this->assertSame([], $loopsB);
        $this->assertNotEmpty($query->listLoops($wsA));
    }

    public function test_run_cycle_api_returns_202_envelope(): void
    {
        $workspaceId = $this->createWorkspace();
        $this->createBrandProfile($workspaceId, ['founders'], []);

        $response = $this->withHeader('X-Workspace-Id', $workspaceId)
            ->postJson('/api/v1/optimization/cycles/run', [
                'lookback_days' => 30,
                'comparison_days' => 30,
            ]);

        $response->assertStatus(202)
            ->assertJsonPath('data.cycle_number', 1)
            ->assertJsonStructure([
                'data' => [
                    'loop',
                    'cycle_number',
                    'snapshots_created',
                    'recommendations_synced',
                    'counts_by_engine',
                    'snapshots',
                ],
            ]);
    }

    private function createWorkspace(): string
    {
        $workspaceId = Str::uuid()->toString();
        \Illuminate\Support\Facades\DB::table('workspaces')->insert([
            'id' => $workspaceId,
            'name' => 'Opt WS',
            'slug' => 'opt-'.substr($workspaceId, 0, 6),
            'plan_id' => null,
            'settings' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $workspaceId;
    }

    /**
     * @param  list<string>  $segments
     * @param  list<string>  $hookPatterns
     */
    private function createBrandProfile(string $workspaceId, array $segments, array $hookPatterns): void
    {
        BrandProfile::query()->create([
            'workspace_id' => $workspaceId,
            'name' => 'Primary',
            'brand_voice' => 'Direct',
            'tone_profile' => ['primary' => 'direct'],
            'target_audience' => [
                'summary' => 'Founders',
                'segments' => $segments,
            ],
            'preferred_ctas' => ['Book a call', 'Comment PLAYBOOK'],
            'preferred_hook_patterns' => $hookPatterns,
            'style_guidelines' => [],
            'memory_version' => 1,
            'is_primary' => true,
            'metadata' => [],
        ]);
    }
}
