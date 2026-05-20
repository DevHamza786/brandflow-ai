<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domains\Analytics\Actions\GetAnalyticsDashboardAction;
use App\Http\Controllers\Api\V1\Concerns\RespondsWithApiEnvelope;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AnalyticsDashboardRequest;
use Illuminate\Http\JsonResponse;

final class AnalyticsDashboardController extends Controller
{
    use RespondsWithApiEnvelope;

    public function show(
        AnalyticsDashboardRequest $request,
        GetAnalyticsDashboardAction $action,
    ): JsonResponse {
        $workspaceId = (string) $request->attributes->get('workspace_id');

        $dto = $action->execute(
            workspaceId: $workspaceId,
            preset: $request->preset(),
            from: $request->from(),
            to: $request->to(),
        );

        return $this->success($dto->toArray());
    }
}
