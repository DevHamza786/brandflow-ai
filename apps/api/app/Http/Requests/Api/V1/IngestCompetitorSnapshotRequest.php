<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

final class IngestCompetitorSnapshotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'payload' => ['required', 'array'],
            'payload.posts' => ['required', 'array', 'min:1'],
            'payload.posts.*.hook_text' => ['nullable', 'string'],
            'payload.posts.*.published_at' => ['nullable', 'date'],
            'payload.posts.*.impressions' => ['nullable', 'integer', 'min:0'],
            'payload.posts.*.likes' => ['nullable', 'integer', 'min:0'],
            'payload.posts.*.comments' => ['nullable', 'integer', 'min:0'],
            'captured_at' => ['nullable', 'date'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return $this->validated('payload');
    }
}
