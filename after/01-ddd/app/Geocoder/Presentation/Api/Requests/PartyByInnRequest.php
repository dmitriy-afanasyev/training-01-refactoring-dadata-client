<?php

declare(strict_types=1);

namespace App\Geocoder\Presentation\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PartyByInnRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'inn' => ['required', 'string', 'digits_between:10,12'],
        ];
    }

    public function getInn(): string
    {
        return $this->validated('inn');
    }
}
