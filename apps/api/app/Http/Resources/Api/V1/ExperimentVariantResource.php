<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Domains\Experimentation\Data\ExperimentVariantDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property-read ExperimentVariantDto $resource */
final class ExperimentVariantResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $dto = $this->resource;

        return [
            'id' => $dto->id,
            'variant_key' => $dto->variantKey,
            'label' => $dto->label,
            'is_control' => $dto->isControl,
            'traffic_weight' => $dto->trafficWeight,
            'payload' => $dto->payload,
            'assignment_count' => $dto->assignmentCount,
        ];
    }
}
