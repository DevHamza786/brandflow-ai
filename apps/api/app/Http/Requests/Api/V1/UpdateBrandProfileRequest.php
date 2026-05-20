<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Domains\Brand\Data\AudienceProfileDto;
use App\Domains\Brand\Data\StyleGuidelinesDto;
use App\Domains\Brand\Data\ToneProfileDto;
use App\Domains\Brand\Data\UpdateBrandProfileDto;
use App\Http\Middleware\ResolveWorkspace;
use Illuminate\Foundation\Http\FormRequest;
final class UpdateBrandProfileRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:120'],
            'brand_voice' => ['sometimes', 'nullable', 'string', 'max:4000'],
            'tone_profile' => ['sometimes', 'array'],
            'tone_profile.primary' => ['sometimes', 'string', 'max:64'],
            'tone_profile.traits' => ['sometimes', 'array'],
            'tone_profile.traits.*' => ['string', 'max:64'],
            'tone_profile.avoid' => ['sometimes', 'array'],
            'tone_profile.avoid.*' => ['string', 'max:64'],
            'tone_profile.formality' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:1'],
            'tone_profile.energy' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:1'],
            'target_audience' => ['sometimes', 'array'],
            'target_audience.summary' => ['sometimes', 'string', 'max:500'],
            'target_audience.segments' => ['sometimes', 'array'],
            'target_audience.segments.*' => ['string', 'max:120'],
            'target_audience.pain_points' => ['sometimes', 'array'],
            'target_audience.pain_points.*' => ['string', 'max:200'],
            'target_audience.goals' => ['sometimes', 'array'],
            'target_audience.goals.*' => ['string', 'max:200'],
            'banned_phrases' => ['sometimes', 'array', 'max:50'],
            'banned_phrases.*' => ['string', 'max:120'],
            'preferred_ctas' => ['sometimes', 'array', 'max:20'],
            'preferred_ctas.*' => ['string', 'max:200'],
            'preferred_hook_patterns' => ['sometimes', 'array', 'max:20'],
            'preferred_hook_patterns.*' => ['string', 'max:120'],
            'style_guidelines' => ['sometimes', 'array'],
            'style_guidelines.summary' => ['sometimes', 'string', 'max:1000'],
            'style_guidelines.do' => ['sometimes', 'array'],
            'style_guidelines.do.*' => ['string', 'max:200'],
            'style_guidelines.dont' => ['sometimes', 'array'],
            'style_guidelines.dont.*' => ['string', 'max:200'],
            'style_guidelines.max_hook_length' => ['sometimes', 'nullable', 'integer', 'min:40', 'max:400'],
            'style_guidelines.use_emojis' => ['sometimes', 'nullable', 'boolean'],
            'metadata' => ['sometimes', 'array'],
            'is_primary' => ['sometimes', 'boolean'],
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

    public function toUpdateDto(): UpdateBrandProfileDto
    {
        $validated = $this->validated();

        return new UpdateBrandProfileDto(
            name: array_key_exists('name', $validated) ? (string) $validated['name'] : null,
            brandVoice: array_key_exists('brand_voice', $validated) ? (string) ($validated['brand_voice'] ?? '') : null,
            toneProfile: isset($validated['tone_profile'])
                ? ToneProfileDto::fromArray($validated['tone_profile'])
                : null,
            targetAudience: isset($validated['target_audience'])
                ? AudienceProfileDto::fromArray($validated['target_audience'])
                : null,
            bannedPhrases: isset($validated['banned_phrases'])
                ? $this->stringList($validated['banned_phrases'])
                : null,
            preferredCtas: isset($validated['preferred_ctas'])
                ? $this->stringList($validated['preferred_ctas'])
                : null,
            preferredHookPatterns: isset($validated['preferred_hook_patterns'])
                ? $this->stringList($validated['preferred_hook_patterns'])
                : null,
            styleGuidelines: isset($validated['style_guidelines'])
                ? StyleGuidelinesDto::fromArray($validated['style_guidelines'])
                : null,
            metadata: $validated['metadata'] ?? null,
            isPrimary: isset($validated['is_primary']) ? (bool) $validated['is_primary'] : null,
        );
    }

    /**
     * @param  list<mixed>  $items
     * @return list<string>
     */
    private function stringList(array $items): array
    {
        return array_values(array_filter(array_map(
            static fn ($v) => is_string($v) ? trim($v) : '',
            $items,
        )));
    }
}
