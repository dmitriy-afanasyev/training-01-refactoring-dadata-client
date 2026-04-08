<?php

declare(strict_types=1);

namespace App\Geocoder\Presentation\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressSearchRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'query' => ['required', 'string', 'min:1', 'max:255'],
            'locations' => ['nullable', 'array'],
            'locations.*' => ['array'],
        ];
    }

    public function getQuery(): string
    {
        return $this->validated('query');
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getLocations(): ?array
    {
        return $this->validated('locations');
    }
}
