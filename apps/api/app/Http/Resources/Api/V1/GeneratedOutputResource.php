<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Domains\AI\Contracts\GeneratedOutputSerializerContract;
use App\Domains\AI\Data\GeneratedOutputDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property GeneratedOutputDto $resource
 */
final class GeneratedOutputResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $serializer = app(GeneratedOutputSerializerContract::class);

        return $serializer->toArray($this->resource);
    }
}
