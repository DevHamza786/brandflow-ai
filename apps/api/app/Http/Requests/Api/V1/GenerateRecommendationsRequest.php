<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

final class GenerateRecommendationsRequest extends FormRequest
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
            'lookback_days' => ['nullable', 'integer', 'min:7', 'max:365'],
        ];
    }

    public function lookbackDays(): ?int
    {
        $v = $this->validated('lookback_days');

        return is_numeric($v) ? (int) $v : null;
    }
}
