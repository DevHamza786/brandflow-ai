<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domains\Brand\Services\BrandProfileManagementService;
use App\Http\Controllers\Api\V1\Concerns\RespondsWithApiEnvelope;
use App\Http\Controllers\Controller;
use App\Http\Middleware\ResolveWorkspace;
use App\Http\Requests\Api\V1\UpdateBrandProfileRequest;
use App\Http\Resources\Api\V1\BrandMemoryPreviewResource;
use App\Http\Resources\Api\V1\BrandProfileResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class BrandProfileController extends Controller
{
    use RespondsWithApiEnvelope;

    public function __construct(
        private readonly BrandProfileManagementService $brand,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $workspaceId = (string) $request->attributes->get(ResolveWorkspace::ATTRIBUTE);
        $profiles = $this->brand->listProfiles($workspaceId);

        return $this->success([
            'profiles' => BrandProfileResource::collection($profiles)->resolve(),
        ]);
    }

    public function primary(Request $request): JsonResponse
    {
        $workspaceId = (string) $request->attributes->get(ResolveWorkspace::ATTRIBUTE);
        $profile = $this->brand->getOrCreatePrimary($workspaceId);

        return $this->success(BrandProfileResource::make($profile));
    }

    public function show(Request $request, string $profileId): JsonResponse
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

        return $this->success(BrandProfileResource::make($profile));
    }

    public function update(UpdateBrandProfileRequest $request): JsonResponse
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

        $updated = $this->brand->updateProfile(
            $request->workspaceId(),
            $request->profileId(),
            $request->toUpdateDto(),
        );

        return $this->success(BrandProfileResource::make($updated));
    }

    public function setPrimary(Request $request, string $profileId): JsonResponse
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

        $updated = $this->brand->setPrimary($workspaceId, $profileId);

        return $this->success(BrandProfileResource::make($updated));
    }

    public function memoryPreview(Request $request, string $profileId): JsonResponse
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

        $query = (string) $request->query('query', 'LinkedIn hook for founders');
        $targetAudience = $request->query('target_audience');
        $contentPillar = $request->query('content_pillar');

        $ctx = $this->brand->memoryPreview(
            $workspaceId,
            $query,
            is_string($targetAudience) ? $targetAudience : null,
            is_string($contentPillar) ? $contentPillar : null,
        );

        return $this->success(BrandMemoryPreviewResource::make($ctx));
    }
}
