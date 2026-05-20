<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domains\Optimization\Actions\RunOptimizationCycleAction;
use App\Domains\Optimization\Services\OptimizationQueryService;
use App\Http\Controllers\Api\V1\Concerns\RespondsWithApiEnvelope;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\RunOptimizationCycleRequest;
use App\Http\Resources\Api\V1\OptimizationLoopResource;
use App\Http\Resources\Api\V1\OptimizationSnapshotResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class OptimizationController extends Controller
{
    use RespondsWithApiEnvelope;

    public function indexLoops(Request $request, OptimizationQueryService $query): JsonResponse
    {
        $workspaceId = (string) $request->attributes->get('workspace_id');
        $loops = $query->listLoops($workspaceId);

        return $this->success([
            'loops' => OptimizationLoopResource::collection($loops)->resolve($request),
        ]);
    }

    public function showLoop(
        string $loopId,
        Request $request,
        OptimizationQueryService $query,
    ): JsonResponse {
        $workspaceId = (string) $request->attributes->get('workspace_id');
        $loop = $query->findLoop($workspaceId, $loopId);

        if ($loop === null) {
            return $this->problem(404, 'optimization_loop_not_found', 'Not found', 'Optimization loop not found.');
        }

        return $this->success(OptimizationLoopResource::make($loop));
    }

    public function indexSnapshots(Request $request, OptimizationQueryService $query): JsonResponse
    {
        $workspaceId = (string) $request->attributes->get('workspace_id');
        $loopId = $request->query('loop_id');
        $snapshots = is_string($loopId) && $loopId !== ''
            ? $query->listSnapshotsByLoop($workspaceId, $loopId)
            : $query->listRecentSnapshots($workspaceId);

        return $this->success([
            'snapshots' => OptimizationSnapshotResource::collection($snapshots)->resolve($request),
        ]);
    }

    public function showSnapshot(
        string $snapshotId,
        Request $request,
        OptimizationQueryService $query,
    ): JsonResponse {
        $workspaceId = (string) $request->attributes->get('workspace_id');
        $snapshot = $query->findSnapshot($workspaceId, $snapshotId);

        if ($snapshot === null) {
            return $this->problem(404, 'optimization_snapshot_not_found', 'Not found', 'Optimization snapshot not found.');
        }

        return $this->success(OptimizationSnapshotResource::make($snapshot));
    }

    public function runCycle(
        RunOptimizationCycleRequest $request,
        RunOptimizationCycleAction $action,
    ): JsonResponse {
        $workspaceId = (string) $request->attributes->get('workspace_id');

        $result = $action->execute(
            $workspaceId,
            $request->lookbackDays(),
            $request->comparisonDays(),
        );

        return $this->success([
            'loop' => OptimizationLoopResource::make($result->loop)->resolve($request),
            'cycle_number' => $result->cycleNumber,
            'snapshots_created' => $result->snapshotsCreated,
            'recommendations_synced' => $result->recommendationsSynced,
            'counts_by_engine' => $result->countsByEngine,
            'snapshots' => OptimizationSnapshotResource::collection($result->snapshots)->resolve($request),
        ], 202);
    }
}
