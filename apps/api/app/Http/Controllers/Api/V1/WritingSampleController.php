<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domains\Brand\Services\BrandProfileManagementService;
use App\Http\Controllers\Api\V1\Concerns\RespondsWithApiEnvelope;
use App\Http\Controllers\Controller;
use App\Http\Middleware\ResolveWorkspace;
use App\Http\Requests\Api\V1\StoreWritingSampleRequest;
use App\Http\Requests\Api\V1\UpdateWritingSampleRequest;
use App\Http\Resources\Api\V1\WritingSampleResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class WritingSampleController extends Controller
{
    use RespondsWithApiEnvelope;

    public function __construct(
        private readonly BrandProfileManagementService $brand,
    ) {
    }

    public function index(Request $request, string $profileId): JsonResponse
    {
        $workspaceId = (string) $request->attributes->get(ResolveWorkspace::ATTRIBUTE);
        $profile = $this->brand->getProfile($workspaceId, $profileId);

        if ($profile === null) {
            return $this->problem(
                status: 404,
                type: 'https://pbos.dev/problems/brand-profile-not-found',
                title: 'Brand Profile Not Found',
                detail: 'The requested brand profile does not exist in this workspace.',
            );
        }

        $samples = $this->brand->listWritingSamples($workspaceId, $profileId);

        return $this->success([
            'samples' => WritingSampleResource::collection($samples)->resolve(),
        ]);
    }

    public function store(StoreWritingSampleRequest $request): JsonResponse
    {
        $profile = $this->brand->getProfile($request->workspaceId(), $request->profileId());

        if ($profile === null) {
            return $this->problem(
                status: 404,
                type: 'https://pbos.dev/problems/brand-profile-not-found',
                title: 'Brand Profile Not Found',
                detail: 'The requested brand profile does not exist in this workspace.',
            );
        }

        $sample = $this->brand->createWritingSample($request->toCreateDto());

        return $this->success(WritingSampleResource::make($sample), 201);
    }

    public function update(UpdateWritingSampleRequest $request): JsonResponse
    {
        $sample = $this->brand->updateWritingSample(
            $request->workspaceId(),
            $request->sampleId(),
            $request->toUpdateDto(),
        );

        return $this->success(WritingSampleResource::make($sample));
    }

    public function destroy(Request $request, string $sampleId): JsonResponse
    {
        $workspaceId = (string) $request->attributes->get(ResolveWorkspace::ATTRIBUTE);
        $this->brand->deleteWritingSample($workspaceId, $sampleId);

        return $this->success(['deleted' => true]);
    }
}
