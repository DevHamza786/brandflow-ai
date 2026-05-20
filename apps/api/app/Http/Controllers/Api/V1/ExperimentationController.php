<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domains\Experimentation\Actions\AssignExperimentVariantAction;
use App\Domains\Experimentation\Actions\CompareExperimentAction;
use App\Domains\Experimentation\Enums\ExperimentType;
use App\Domains\Experimentation\Services\ExperimentQueryService;
use App\Domains\Experimentation\Services\ExperimentationEngine;
use App\Http\Controllers\Api\V1\Concerns\RespondsWithApiEnvelope;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ExperimentResource;
use App\Http\Resources\Api\V1\ExperimentVariantResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ExperimentationController extends Controller
{
    use RespondsWithApiEnvelope;

    public function index(Request $request, ExperimentQueryService $query): JsonResponse
    {
        $workspaceId = (string) $request->attributes->get('workspace_id');

        return $this->success([
            'experiments' => ExperimentResource::collection($query->listExperiments($workspaceId))->resolve($request),
        ]);
    }

    public function show(string $experimentId, Request $request, ExperimentQueryService $query): JsonResponse
    {
        $workspaceId = (string) $request->attributes->get('workspace_id');
        $experiment = $query->findExperiment($workspaceId, $experimentId);

        if ($experiment === null) {
            return $this->problem(404, 'experiment_not_found', 'Not found', 'Experiment not found.');
        }

        return $this->success([
            'experiment' => ExperimentResource::make($experiment),
            'variants' => ExperimentVariantResource::collection(
                $query->listVariants($workspaceId, $experimentId),
            )->resolve($request),
        ]);
    }

    public function assign(Request $request, AssignExperimentVariantAction $action): JsonResponse
    {
        $workspaceId = (string) $request->attributes->get('workspace_id');
        $type = ExperimentType::from((string) $request->input('experiment_type', 'hook_ab'));
        $subjectKey = (string) $request->input('subject_key', 'subject:default');

        $assignment = $action->execute($workspaceId, $type, $subjectKey);

        return $this->success([
            'experiment_id' => $assignment->experimentId,
            'subject_key' => $assignment->subjectKey,
            'was_existing' => $assignment->wasExisting,
            'variant' => ExperimentVariantResource::make($assignment->variant),
        ], 201);
    }

    public function compare(string $experimentId, Request $request, CompareExperimentAction $action): JsonResponse
    {
        $workspaceId = (string) $request->attributes->get('workspace_id');
        $comparison = $action->execute($workspaceId, $experimentId);

        return $this->success([
            'experiment_id' => $comparison->experimentId,
            'winner_variant_key' => $comparison->winnerVariantKey,
            'lift_percent' => $comparison->liftPercent,
            'confidence' => $comparison->confidence,
            'is_significant' => $comparison->isSignificant,
            'narrative' => $comparison->narrative,
            'control_samples' => $comparison->controlSamples,
            'variant_samples' => $comparison->variantSamples,
        ]);
    }

    public function runDemoCycle(Request $request, ExperimentationEngine $engine): JsonResponse
    {
        $workspaceId = (string) $request->attributes->get('workspace_id');
        $type = ExperimentType::from((string) $request->input('experiment_type', 'hook_ab'));

        $experiment = $engine->ensureExperiment($workspaceId, $type);
        $assignA = $engine->assignVariant($workspaceId, $type, 'subject:demo-a');
        $assignB = $engine->assignVariant($workspaceId, $type, 'subject:demo-b');

        $engine->recordObservation($workspaceId, $experiment->id, $assignA->variant->id, 'subject:demo-a', [
            'impressions' => 1000,
            'engagements' => 40,
            'normalized_score' => 4.0,
        ]);
        $engine->recordObservation($workspaceId, $experiment->id, $assignB->variant->id, 'subject:demo-b', [
            'impressions' => 1000,
            'engagements' => 52,
            'normalized_score' => 5.2,
        ]);

        $comparison = $engine->compareExperiment($workspaceId, $experiment->id);

        return $this->success([
            'experiment_id' => $experiment->id,
            'assignments' => [
                ['subject' => 'subject:demo-a', 'variant' => $assignA->variant->variantKey],
                ['subject' => 'subject:demo-b', 'variant' => $assignB->variant->variantKey],
            ],
            'comparison' => $comparison->toArray(),
        ], 202);
    }
}
