<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

final class RunOptimizationCycleRequest extends FormRequest
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
            'lookback_days' => ['nullable', 'integer', 'min:7', 'max:90'],
            'comparison_days' => ['nullable', 'integer', 'min:7', 'max:90'],
        ];
    }

    public function lookbackDays(): ?int
    {
        $v = $this->validated('lookback_days');

        return is_numeric($v) ? (int) $v : null;
    }

    public function comparisonDays(): ?int
    {
        $v = $this->validated('comparison_days');

        return is_numeric($v) ? (int) $v : null;
    }
}
