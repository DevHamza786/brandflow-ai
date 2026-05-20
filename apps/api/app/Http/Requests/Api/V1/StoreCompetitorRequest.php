<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

final class StoreCompetitorRequest extends FormRequest
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
            'linkedin_url' => ['required', 'string', 'max:512', 'url'],
            'name' => ['nullable', 'string', 'max:255'],
            'linkedin_urn' => ['nullable', 'string', 'max:255'],
            'labels' => ['nullable', 'array'],
            'labels.*' => ['string', 'max:64'],
            'metadata' => ['nullable', 'array'],
            'scrape_cadence_hours' => ['nullable', 'integer', 'min:1', 'max:720'],
        ];
    }
}
