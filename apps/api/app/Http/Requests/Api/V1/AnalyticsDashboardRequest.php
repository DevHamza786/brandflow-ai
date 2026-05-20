<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class AnalyticsDashboardRequest extends FormRequest
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
            'preset' => ['nullable', 'string', Rule::in(['7d', '30d', '90d'])],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ];
    }

    public function preset(): ?string
    {
        $v = $this->validated('preset');

        return is_string($v) ? $v : null;
    }

    public function from(): ?string
    {
        $v = $this->validated('from');

        return is_string($v) ? $v : null;
    }

    public function to(): ?string
    {
        $v = $this->validated('to');

        return is_string($v) ? $v : null;
    }
}
