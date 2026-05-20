<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Domains\Recommendations\Enums\RecommendationType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ListRecommendationsRequest extends FormRequest
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
            'type' => ['nullable', 'string', Rule::enum(RecommendationType::class)],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function type(): ?RecommendationType
    {
        $v = $this->validated('type');

        return is_string($v) ? RecommendationType::from($v) : null;
    }

    public function limit(): int
    {
        return (int) ($this->validated('limit') ?? 50);
    }
}
