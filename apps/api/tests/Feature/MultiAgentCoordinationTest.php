<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Coordination\Actions\RunCoordinationCycleAction;
use App\Domains\Coordination\Enums\CoordinationSnapshotType;
use App\Domains\Coordination\Enums\CoordinationStatus;
use App\Domains\Coordination\Models\AgentCoordination;
use App\Domains\Coordination\Models\AgentCoordinationSnapshot;
use App\Domains\Coordination\Services\AgentRoutingEngine;
use App\Domains\Coordination\Data\CoordinationTaskDto;
use App\Domains\Coordination\Enums\CoordinationRole;
use App\Domains\Coordination\Enums\CoordinationTaskType;
use App\Domains\Coordination\Services\CoordinationQueryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class MultiAgentCoordinationTest extends TestCase
{
    use RefreshDatabase;

    public function test_agent_routing_selects_correct_agents_by_task(): void
    {
        $routing = app(AgentRoutingEngine::class);

        $analytics = $routing->resolve(new CoordinationTaskDto(
            taskType: CoordinationTaskType::AnalyticsInsights,
            role: CoordinationRole::Analytics,
        ));
        $this->assertSame('analytics', $analytics->agentSlug);

        $strategist = $routing->resolve(new CoordinationTaskDto(
            taskType: CoordinationTaskType::StrategistPlan,
            role: CoordinationRole::Strategist,
        ));
        $this->assertSame('hook', $strategist->agentSlug);

        $competitor = $routing->resolve(new CoordinationTaskDto(
            taskType: CoordinationTaskType::CompetitorAnalysis,
            role: CoordinationRole::Competitor,
        ));
        $this->assertSame('competitor', $competitor->agentSlug);

        $optimization = $routing->resolve(new CoordinationTaskDto(
            taskType: CoordinationTaskType::OptimizationCycle,
            role: CoordinationRole::Optimization,
        ));
        $this->assertSame('optimization', $optimization->handlerType->value);
    }

    public function test_coordination_cycle_persists_routing_and_context_snapshots(): void
    {
        $workspaceId = $this->createWorkspace();

        $result = app(RunCoordinationCycleAction::class)->execute($workspaceId);

        $this->assertGreaterThan(0, $result->snapshotsCreated);
        $this->assertGreaterThan(0, $result->tasksCompleted);

        $routing = AgentCoordinationSnapshot::query()
            ->where('workspace_id', $workspaceId)
            ->where('cycle_number', $result->cycleNumber)
            ->where('snapshot_type', CoordinationSnapshotType::Routing->value)
            ->count();
        $this->assertGreaterThan(0, $routing);

        $context = AgentCoordinationSnapshot::query()
            ->where('workspace_id', $workspaceId)
            ->where('snapshot_type', CoordinationSnapshotType::ContextShare->value)
            ->first();
        $this->assertNotNull($context);
        $refs = $context->context_refs ?? [];
        $this->assertArrayHasKey('context_digest', $refs);
        $this->assertArrayNotHasKey('full_prompt', $refs);
    }

    public function test_failure_isolation_allows_partial_success(): void
    {
        config(['coordination.test_force_fail_tasks' => ['analytics_insights']]);
        $workspaceId = $this->createWorkspace();

        $result = app(RunCoordinationCycleAction::class)->execute($workspaceId);

        $this->assertContains('analytics_insights', $result->failedTasks);
        $this->assertGreaterThan(0, $result->tasksCompleted);

        $coordination = AgentCoordination::query()
            ->where('workspace_id', $workspaceId)
            ->first();
        $this->assertSame(CoordinationStatus::PartialSuccess->value, $coordination->status);
    }

    public function test_routing_preview_api(): void
    {
        $workspaceId = $this->createWorkspace();

        $this->withHeader('X-Workspace-Id', $workspaceId)
            ->getJson('/api/v1/coordination/routing/preview?task_type=publishing_schedule&role=publishing')
            ->assertOk()
            ->assertJsonPath('data.handler_type', 'publishing');
    }

    public function test_default_coordination_session_exists(): void
    {
        $workspaceId = $this->createWorkspace();
        $dto = app(CoordinationQueryService::class)->defaultCoordination($workspaceId);
        $this->assertSame('coordination:workspace:default', $dto->correlationKey);
    }

    private function createWorkspace(): string
    {
        $workspaceId = Str::uuid()->toString();
        \Illuminate\Support\Facades\DB::table('workspaces')->insert([
            'id' => $workspaceId,
            'name' => 'Coord WS',
            'slug' => 'coord-'.substr($workspaceId, 0, 6),
            'plan_id' => null,
            'settings' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $workspaceId;
    }
}
