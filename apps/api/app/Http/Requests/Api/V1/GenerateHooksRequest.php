<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Domains\Agents\Agents\HookAgent\HookAgentConfig;
use App\Http\Middleware\ResolveWorkspace;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class GenerateHooksRequest extends FormRequest
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
            'input' => ['sometimes', 'array'],
            'input.content_version_id' => [
                'sometimes',
                'uuid',
                Rule::in([(string) $this->route('versionId')]),
            ],
            'options' => ['sometimes', 'array'],
            'options.max_variants' => ['sometimes', 'integer', 'min:1', 'max:10'],
            'options.target_audience' => ['sometimes', 'nullable', 'string', 'max:500'],
            'options.content_pillar' => ['sometimes', 'nullable', 'string', 'max:255'],
            'options.provider' => ['sometimes', 'nullable', 'string', Rule::in(['openai', 'gemini'])],
            'options.model' => ['sometimes', 'nullable', 'string', 'max:128'],
            'options.scorer_prompt_version' => ['sometimes', 'string', 'max:16'],
            'options.generator_prompt_version' => ['sometimes', 'string', 'max:16'],
            'options.experiment_id' => ['sometimes', 'nullable', 'string', 'max:128'],
            'options.memory_version' => ['sometimes', 'nullable', 'integer', 'min:1'],
        ];
    }

    public function workspaceId(): string
    {
        return (string) $this->attributes->get(ResolveWorkspace::ATTRIBUTE);
    }

    public function contentVersionId(): string
    {
        return (string) $this->route('versionId');
    }

    public function idempotencyKey(): ?string
    {
        $key = $this->header('Idempotency-Key');

        return is_string($key) && $key !== '' ? $key : null;
    }

    public function toHookAgentConfig(): HookAgentConfig
    {
        /** @var array<string, mixed> $options */
        $options = $this->validated('options') ?? [];

        return new HookAgentConfig(
            contentVersionId: $this->contentVersionId(),
            maxVariants: (int) ($options['max_variants'] ?? 3),
            targetAudience: isset($options['target_audience']) ? (string) $options['target_audience'] : null,
            contentPillar: isset($options['content_pillar']) ? (string) $options['content_pillar'] : null,
            provider: isset($options['provider']) ? (string) $options['provider'] : null,
            model: isset($options['model']) ? (string) $options['model'] : null,
            scorerPromptVersion: (string) ($options['scorer_prompt_version'] ?? 'v1'),
            generatorPromptVersion: (string) ($options['generator_prompt_version'] ?? 'v1'),
            experimentId: isset($options['experiment_id']) ? (string) $options['experiment_id'] : null,
            memoryVersion: isset($options['memory_version']) ? (int) $options['memory_version'] : null,
        );
    }
}
