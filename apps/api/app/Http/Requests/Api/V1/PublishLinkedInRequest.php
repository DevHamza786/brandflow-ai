<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Http\Middleware\ResolveWorkspace;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class PublishLinkedInRequest extends FormRequest
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
            'linkedin_integration_id' => ['required', 'uuid'],
            'content' => ['nullable', 'string', 'max:8000'],
            'generated_output_id' => ['nullable', 'uuid'],
            'scheduled_for' => ['nullable', 'date'],
        ];
    }

    public function workspaceId(): string
    {
        return (string) $this->attributes->get(ResolveWorkspace::ATTRIBUTE);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->filled('content')) {
                return;
            }
            if ($this->filled('generated_output_id')) {
                return;
            }
            $validator->errors()->add('content', 'Provide content or generated_output_id.');
            $validator->errors()->add('generated_output_id', 'Provide content or generated_output_id.');
        });
    }
}
