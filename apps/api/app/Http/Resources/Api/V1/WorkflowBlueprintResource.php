<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Domains\WorkflowBuilder\Data\WorkflowBlueprintDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property-read WorkflowBlueprintDto $resource */
final class WorkflowBlueprintResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $dto = $this->resource;

        return [
            'id' => $dto->id,
            'workspace_id' => $dto->workspaceId,
            'slug' => $dto->slug,
            'name' => $dto->name,
            'status' => $dto->status->value,
            'version' => $dto->version,
            'is_active' => $dto->isActive,
            'blueprint_type' => $dto->blueprintType,
            'config' => $dto->config,
        ];
    }
}
