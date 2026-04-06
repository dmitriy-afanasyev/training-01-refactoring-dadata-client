<?php

declare(strict_types=1);

namespace App\Geocoder\Presentation\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CountrySearchRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'query' => ['required', 'string', 'min:1', 'max:255'],
        ];
    }

    public function getQuery(): string
    {
        return $this->validated('query');
    }
}
