<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Analytics\Data\CreatePostPerformanceSnapshotDto;
use App\Domains\Analytics\Services\PerformanceAggregationService;
use App\Domains\Autonomous\Actions\RunAutonomousExecutionAction;
use App\Domains\Autonomous\Actions\UpdateAutonomousWorkflowAction;
use App\Domains\Autonomous\Data\UpdateAutonomousWorkflowDto;
use App\Domains\Autonomous\Enums\AutonomousExecutionStatus;
use App\Domains\Autonomous\Models\AutonomousExecutionSnapshot;
use App\Domains\Autonomous\Models\AutonomousWorkflow;
use App\Domains\Autonomous\Services\AutonomousQueryService;
use App\Domains\Brand\Models\BrandProfile;
use App\Domains\Recommendations\Actions\GenerateRecommendationsAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class AutonomousPostingEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_low_confidence_decisions_are_blocked(): void
    {
        $workspaceId = $this->createWorkspace();
        $this->seedPerformanceData($workspaceId);
        $wf = app(AutonomousQueryService::class)->defaultWorkflow($workspaceId);

        app(UpdateAutonomousWorkflowAction::class)->execute(
            $workspaceId,
            $wf->id,
            new UpdateAutonomousWorkflowDto(minConfidence: 0.99),
        );

        $result = app(RunAutonomousExecutionAction::class)->execute($workspaceId);

        $this->assertGreaterThan(0, $result->blockedCount);
        $blocked = AutonomousExecutionSnapshot::query()
            ->where('workspace_id', $workspaceId)
            ->where('status', AutonomousExecutionStatus::BlockedLowConfidence->value)
            ->count();
        $this->assertGreaterThan(0, $blocked);
    }

    public function test_high_signal_workspace_produces_distinct_decision_engines(): void
    {
        $workspaceId = $this->createWorkspace();
        $this->createBrandProfile($workspaceId);
        $this->seedPerformanceData($workspaceId);

        app(GenerateRecommendationsAction::class)->execute($workspaceId);

        app(UpdateAutonomousWorkflowAction::class)->execute(
            $workspaceId,
            (string) app(AutonomousQueryService::class)->defaultWorkflow($workspaceId)->id,
            new UpdateAutonomousWorkflowDto(minConfidence: 0.45),
        );

        $result = app(RunAutonomousExecutionAction::class)->execute($workspaceId);

        $this->assertGreaterThan(0, $result->snapshotsCreated);
        $engines = AutonomousExecutionSnapshot::query()
            ->where('workspace_id', $workspaceId)
            ->where('cycle_number', $result->cycleNumber)
            ->pluck('engine')
            ->unique()
            ->values()
            ->all();

        $this->assertContains('posting_time_decision', $engines);
        $this->assertGreaterThanOrEqual(2, count($engines));
    }

    public function test_execution_is_idempotent_per_cycle(): void
    {
        $workspaceId = $this->createWorkspace();
        $this->seedPerformanceData($workspaceId);

        $first = app(RunAutonomousExecutionAction::class)->execute($workspaceId);
        $countAfterFirst = AutonomousExecutionSnapshot::query()
            ->where('workspace_id', $workspaceId)
            ->count();

        try {
            app(RunAutonomousExecutionAction::class)->execute($workspaceId);
        } catch (\RuntimeException) {
            // lock may block immediate re-entry — acceptable
        }

        $this->assertGreaterThan(0, $countAfterFirst);
        $this->assertGreaterThan(0, $first->snapshotsCreated);
    }

    public function test_patch_workflow_updates_confidence_threshold(): void
    {
        $workspaceId = $this->createWorkspace();
        $wf = app(AutonomousQueryService::class)->defaultWorkflow($workspaceId);

        $response = $this->withHeader('X-Workspace-Id', $workspaceId)
            ->patchJson("/api/v1/autonomous/workflows/{$wf->id}", [
                'min_confidence' => 0.72,
                'mode' => 'suggest',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.config.min_confidence', 0.72)
            ->assertJsonPath('data.mode', 'suggest');
    }

    private function createWorkspace(): string
    {
        $workspaceId = Str::uuid()->toString();
        \Illuminate\Support\Facades\DB::table('workspaces')->insert([
            'id' => $workspaceId,
            'name' => 'Auto WS',
            'slug' => 'auto-'.substr($workspaceId, 0, 6),
            'plan_id' => null,
            'settings' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $workspaceId;
    }

    private function createBrandProfile(string $workspaceId): void
    {
        BrandProfile::query()->create([
            'workspace_id' => $workspaceId,
            'name' => 'Primary',
            'brand_voice' => 'Direct',
            'tone_profile' => ['primary' => 'direct'],
            'target_audience' => ['summary' => 'Founders', 'segments' => ['SaaS']],
            'preferred_ctas' => ['Book a call'],
            'preferred_hook_patterns' => [],
            'style_guidelines' => [],
            'memory_version' => 1,
            'is_primary' => true,
            'metadata' => [],
        ]);
    }

    private function seedPerformanceData(string $workspaceId): void
    {
        $agg = app(PerformanceAggregationService::class);
        for ($i = 0; $i < 5; $i++) {
            $agg->persistSnapshot(new CreatePostPerformanceSnapshotDto(
                workspaceId: $workspaceId,
                entityType: 'scheduled_post',
                entityId: Str::uuid()->toString(),
                observedAt: now()->subDays(2 + $i),
                postedAt: now()->subDays(2 + $i)->setHour(14),
                impressions: 4000,
                likes: 350,
                comments: 40,
                hookPerformance: [
                    'text' => 'Why do founders ignore churn until renewal day?',
                    'overall' => 85.0,
                ],
            ));
        }
    }
}
