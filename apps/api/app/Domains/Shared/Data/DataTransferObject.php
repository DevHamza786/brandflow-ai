<?php

declare(strict_types=1);

namespace App\Domains\Shared\Data;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

/**
 * Base immutable-friendly DTO for domain data objects.
 */
abstract class DataTransferObject implements Arrayable, JsonSerializable
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload): static
    {
        return new static(...$payload);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [];

        foreach (get_object_vars($this) as $key => $value) {
            if ($value instanceof Arrayable) {
                $data[$key] = $value->toArray();

                continue;
            }

            if ($value instanceof JsonSerializable) {
                $data[$key] = $value->jsonSerialize();

                continue;
            }

            $data[$key] = $value;
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
