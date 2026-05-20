<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domains\Recommendations\Actions\GenerateRecommendationsAction;
use App\Domains\Recommendations\Services\RecommendationQueryService;
use App\Http\Controllers\Api\V1\Concerns\RespondsWithApiEnvelope;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\GenerateRecommendationsRequest;
use App\Http\Requests\Api\V1\ListRecommendationsRequest;
use App\Http\Resources\Api\V1\RecommendationResource;
use App\Http\Resources\Api\V1\RecommendationSummaryResource;
use Illuminate\Http\JsonResponse;

final class RecommendationsController extends Controller
{
    use RespondsWithApiEnvelope;

    public function index(
        ListRecommendationsRequest $request,
        RecommendationQueryService $query,
    ): JsonResponse {
        $workspaceId = (string) $request->attributes->get('workspace_id');

        $rows = $query->listActive(
            workspaceId: $workspaceId,
            type: $request->type(),
            limit: $request->limit(),
        );

        return $this->success([
            'recommendations' => RecommendationSummaryResource::collection($rows)->resolve($request),
        ]);
    }

    public function show(
        string $recommendationId,
        ListRecommendationsRequest $request,
        RecommendationQueryService $query,
    ): JsonResponse {
        $workspaceId = (string) $request->attributes->get('workspace_id');
        $row = $query->find($workspaceId, $recommendationId);

        if ($row === null) {
            return $this->problem(404, 'recommendation_not_found', 'Not found', 'Recommendation not found.');
        }

        return $this->success(RecommendationResource::make($row));
    }

    public function generate(
        GenerateRecommendationsRequest $request,
        GenerateRecommendationsAction $action,
    ): JsonResponse {
        $workspaceId = (string) $request->attributes->get('workspace_id');

        $result = $action->execute($workspaceId, $request->lookbackDays());

        return $this->success([
            'generated_count' => $result->generatedCount,
            'superseded_count' => $result->supersededCount,
            'counts_by_type' => $result->countsByType,
            'recommendations' => RecommendationResource::collection($result->recommendations)->resolve($request),
        ], 202);
    }
}
