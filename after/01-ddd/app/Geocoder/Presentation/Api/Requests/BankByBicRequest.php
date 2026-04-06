<?php

declare(strict_types=1);

namespace App\Geocoder\Presentation\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BankByBicRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'bic' => ['required', 'string', 'digits:9'],
        ];
    }

    public function getBic(): string
    {
        return $this->validated('bic');
    }
}
