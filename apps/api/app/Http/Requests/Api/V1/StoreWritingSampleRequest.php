<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Domains\Brand\Data\CreateWritingSampleDto;
use App\Domains\Brand\Enums\WritingSampleSourceType;
use App\Domains\Brand\Services\BrandProfileManagementService;
use App\Http\Middleware\ResolveWorkspace;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreWritingSampleRequest extends FormRequest
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
            'content' => ['required', 'string', 'min:20', 'max:20000'],
            'source_type' => [
                'sometimes',
                'string',
                Rule::in(array_column(WritingSampleSourceType::cases(), 'value')),
            ],
            'metadata' => ['sometimes', 'array'],
            'extract_style' => ['sometimes', 'boolean'],
        ];
    }

    public function workspaceId(): string
    {
        return (string) $this->attributes->get(ResolveWorkspace::ATTRIBUTE);
    }

    public function profileId(): string
    {
        return (string) $this->route('profileId');
    }

    public function toCreateDto(): CreateWritingSampleDto
    {
        $validated = $this->validated();

        return new CreateWritingSampleDto(
            workspaceId: $this->workspaceId(),
            content: (string) $validated['content'],
            sourceType: BrandProfileManagementService::parseSourceType(
                (string) ($validated['source_type'] ?? 'manual'),
            ),
            brandProfileId: $this->profileId(),
            metadata: $validated['metadata'] ?? [],
            extractStyle: (bool) ($validated['extract_style'] ?? true),
        );
    }
}
