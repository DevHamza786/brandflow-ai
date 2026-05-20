<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Domains\Brand\Data\UpdateWritingSampleDto;
use App\Domains\Brand\Enums\WritingSampleSourceType;
use App\Domains\Brand\Services\BrandProfileManagementService;
use App\Http\Middleware\ResolveWorkspace;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateWritingSampleRequest extends FormRequest
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
            'content' => ['sometimes', 'string', 'min:20', 'max:20000'],
            'source_type' => [
                'sometimes',
                'string',
                Rule::in(array_column(WritingSampleSourceType::cases(), 'value')),
            ],
            'metadata' => ['sometimes', 'array'],
            'reextract_style' => ['sometimes', 'boolean'],
        ];
    }

    public function workspaceId(): string
    {
        return (string) $this->attributes->get(ResolveWorkspace::ATTRIBUTE);
    }

    public function sampleId(): string
    {
        return (string) $this->route('sampleId');
    }

    public function toUpdateDto(): UpdateWritingSampleDto
    {
        $validated = $this->validated();

        return new UpdateWritingSampleDto(
            content: isset($validated['content']) ? (string) $validated['content'] : null,
            sourceType: isset($validated['source_type'])
                ? BrandProfileManagementService::parseSourceType((string) $validated['source_type'])
                : null,
            metadata: $validated['metadata'] ?? null,
            reextractStyle: isset($validated['reextract_style'])
                ? (bool) $validated['reextract_style']
                : null,
        );
    }
}
