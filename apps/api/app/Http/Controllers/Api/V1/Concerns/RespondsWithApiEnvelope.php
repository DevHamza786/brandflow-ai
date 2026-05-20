<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

trait RespondsWithApiEnvelope
{
    protected function success(JsonResource|array $data, int $status = 200): JsonResponse
    {
        $payload = $data instanceof JsonResource
            ? ['data' => $data->resolve(request())]
            : ['data' => $data];

        return response()->json([
            'success' => true,
            ...$payload,
        ], $status);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function problem(
        int $status,
        string $type,
        string $title,
        string $detail,
        array $context = [],
    ): JsonResponse {
        $body = [
            'success' => false,
            'type' => $type,
            'title' => $title,
            'status' => $status,
            'detail' => $detail,
        ];

        if ($context !== []) {
            $body['context'] = $context;
        }

        return response()->json($body, $status, [
            'Content-Type' => 'application/problem+json',
        ]);
    }
}
