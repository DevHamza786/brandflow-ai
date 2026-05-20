<?php

declare(strict_types=1);

namespace App\Domains\AI\Contracts;

use App\Domains\AI\Data\GeneratedOutputDto;

/**
 * API-safe serialization — redacts sensitive metadata before HTTP exposure.
 */
interface GeneratedOutputSerializerContract
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(GeneratedOutputDto $dto): array;

    /**
     * @return array<string, mixed>
     */
    public function toSummary(GeneratedOutputDto $dto): array;
}
