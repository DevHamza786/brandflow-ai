<?php

declare(strict_types=1);

namespace App\Domains\WorkflowBuilder\Repositories;

use App\Domains\WorkflowBuilder\Contracts\WorkflowBlueprintRepositoryContract;
use App\Domains\WorkflowBuilder\Data\WorkflowBlueprintDto;
use App\Domains\WorkflowBuilder\Enums\BlueprintStatus;
use App\Domains\WorkflowBuilder\Enums\WorkflowEdgeType;
use App\Domains\WorkflowBuilder\Enums\WorkflowNodeType;
use App\Domains\WorkflowBuilder\Models\WorkflowBlueprint;
use App\Domains\WorkflowBuilder\Models\WorkflowEdge;
use App\Domains\WorkflowBuilder\Models\WorkflowNode;
use Illuminate\Support\Str;

final class WorkflowBlueprintRepository implements WorkflowBlueprintRepositoryContract
{
    public function findOrCreateDefault(string $workspaceId): WorkflowBlueprintDto
    {
        $slug = (string) config('workflow_builder.default_blueprint_slug', 'multi-agent-default');
        $existing = $this->findBySlug($workspaceId, $slug);

        if ($existing !== null) {
            return $existing;
        }

        $blueprint = WorkflowBlueprint::query()->create([
            'id' => (string) Str::uuid(),
            'workspace_id' => $workspaceId,
            'slug' => $slug,
            'name' => 'Multi-Agent Default',
            'status' => BlueprintStatus::Published->value,
            'version' => 1,
            'is_active' => true,
            'blueprint_type' => 'multi_agent',
            'config' => ['visual_layout' => 'dag_v1'],
        ]);

        $this->seedDefaultGraph($workspaceId, $blueprint->id);

        return $this->toDto($blueprint);
    }

    public function findById(string $workspaceId, string $id): ?WorkflowBlueprintDto
    {
        $model = WorkflowBlueprint::query()
            ->where('workspace_id', $workspaceId)
            ->where('id', $id)
            ->first();

        return $model ? $this->toDto($model) : null;
    }

    public function findBySlug(string $workspaceId, string $slug, ?int $version = null): ?WorkflowBlueprintDto
    {
        $query = WorkflowBlueprint::query()
            ->where('workspace_id', $workspaceId)
            ->where('slug', $slug);

        if ($version !== null) {
            $query->where('version', $version);
        } else {
            $query->orderByDesc('version');
        }

        $model = $query->first();

        return $model ? $this->toDto($model) : null;
    }

    /**
     * @return list<WorkflowBlueprintDto>
     */
    public function listActive(string $workspaceId): array
    {
        return WorkflowBlueprint::query()
            ->where('workspace_id', $workspaceId)
            ->where('is_active', true)
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn ($m) => $this->toDto($m))
            ->all();
    }

    private function seedDefaultGraph(string $workspaceId, string $blueprintId): void
    {
        $definitions = [
            ['key' => 'start_coordination', 'type' => WorkflowNodeType::Coordination, 'order' => 0],
            ['key' => 'analytics_agent', 'type' => WorkflowNodeType::Agent, 'order' => 10, 'config' => ['agent_slug' => 'analytics']],
            ['key' => 'optimization', 'type' => WorkflowNodeType::Optimization, 'order' => 20],
            ['key' => 'autonomous_gate', 'type' => WorkflowNodeType::Autonomous, 'order' => 30],
        ];

        foreach ($definitions as $def) {
            WorkflowNode::query()->create([
                'id' => (string) Str::uuid(),
                'workspace_id' => $workspaceId,
                'workflow_blueprint_id' => $blueprintId,
                'node_key' => $def['key'],
                'node_type' => $def['type']->value,
                'label' => $def['key'],
                'config' => $def['config'] ?? [],
                'position' => ['x' => $def['order'], 'y' => 0],
                'sort_order' => $def['order'],
            ]);
        }

        $edgePairs = [
            ['start_coordination', 'analytics_agent'],
            ['analytics_agent', 'optimization'],
            ['optimization', 'autonomous_gate'],
        ];

        foreach ($edgePairs as [$from, $to]) {
            WorkflowEdge::query()->create([
                'id' => (string) Str::uuid(),
                'workspace_id' => $workspaceId,
                'workflow_blueprint_id' => $blueprintId,
                'from_node_key' => $from,
                'to_node_key' => $to,
                'edge_type' => WorkflowEdgeType::Default->value,
            ]);
        }
    }

    private function toDto(WorkflowBlueprint $model): WorkflowBlueprintDto
    {
        return new WorkflowBlueprintDto(
            id: $model->id,
            workspaceId: $model->workspace_id,
            slug: $model->slug,
            name: $model->name,
            status: BlueprintStatus::from($model->status),
            version: (int) $model->version,
            isActive: (bool) $model->is_active,
            blueprintType: $model->blueprint_type,
            config: $model->config ?? [],
            mlState: $model->ml_state ?? [],
            metadata: $model->metadata ?? [],
        );
    }
}
