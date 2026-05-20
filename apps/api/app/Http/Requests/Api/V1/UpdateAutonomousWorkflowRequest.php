<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateAutonomousWorkflowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'status' => ['nullable', 'string', Rule::in(['active', 'paused', 'disabled'])],
            'mode' => ['nullable', 'string', Rule::in(['observe', 'suggest', 'execute'])],
            'manual_override_enabled' => ['nullable', 'boolean'],
            'autonomous_execution_enabled' => ['nullable', 'boolean'],
            'min_confidence' => ['nullable', 'numeric', 'min:0', 'max:1'],
        ];
    }

    public function minConfidence(): ?float
    {
        $v = $this->validated('min_confidence');

        return is_numeric($v) ? (float) $v : null;
    }
}
