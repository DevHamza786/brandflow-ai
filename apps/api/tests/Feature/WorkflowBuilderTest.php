<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\WorkflowBuilder\Actions\ExecuteWorkflowBlueprintAction;
use App\Domains\WorkflowBuilder\Models\WorkflowBlueprint;
use App\Domains\WorkflowBuilder\Models\WorkflowEdge;
use App\Domains\WorkflowBuilder\Models\WorkflowNode;
use App\Domains\WorkflowBuilder\Services\WorkflowBuilderQueryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class WorkflowBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_blueprint_seeds_graph_nodes_and_edges(): void
    {
        $workspaceId = $this->createWorkspace();
        $blueprint = app(WorkflowBuilderQueryService::class)->defaultBlueprint($workspaceId);

        $nodes = WorkflowNode::query()
            ->where('workflow_blueprint_id', $blueprint->id)
            ->count();
        $edges = WorkflowEdge::query()
            ->where('workflow_blueprint_id', $blueprint->id)
            ->count();

        $this->assertGreaterThanOrEqual(4, $nodes);
        $this->assertGreaterThanOrEqual(3, $edges);
    }

    public function test_blueprint_validation_passes_for_default_graph(): void
    {
        $workspaceId = $this->createWorkspace();
        $blueprint = app(WorkflowBuilderQueryService::class)->defaultBlueprint($workspaceId);

        $result = app(WorkflowBuilderQueryService::class)->validateBlueprint($workspaceId, $blueprint->id);

        $this->assertTrue($result->valid);
        $this->assertSame([], $result->errors);
    }

    public function test_blueprint_execution_runs_nodes_with_isolation(): void
    {
        $workspaceId = $this->createWorkspace();
        $result = app(ExecuteWorkflowBlueprintAction::class)->execute($workspaceId);

        $this->assertGreaterThan(0, $result->nodesExecuted);
        $this->assertContains('start_coordination', $result->executedNodeKeys);
    }

    public function test_blueprint_show_api_returns_graph(): void
    {
        $workspaceId = $this->createWorkspace();
        $blueprint = app(WorkflowBuilderQueryService::class)->defaultBlueprint($workspaceId);

        $this->withHeader('X-Workspace-Id', $workspaceId)
            ->getJson("/api/v1/workflow-builder/blueprints/{$blueprint->id}")
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'blueprint' => ['id', 'slug'],
                    'nodes',
                    'edges',
                ],
            ]);
    }

    public function test_workflow_blueprints_table_has_expected_columns(): void
    {
        $this->assertTrue(
            \Illuminate\Support\Facades\Schema::hasColumns('workflow_blueprints', [
                'id', 'workspace_id', 'slug', 'status', 'version', 'is_active', 'created_at',
            ]),
        );
    }

    private function createWorkspace(): string
    {
        $workspaceId = Str::uuid()->toString();
        \Illuminate\Support\Facades\DB::table('workspaces')->insert([
            'id' => $workspaceId,
            'name' => 'WFB WS',
            'slug' => 'wfb-'.substr($workspaceId, 0, 6),
            'plan_id' => null,
            'settings' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $workspaceId;
    }
}
