<?php

declare(strict_types=1);

namespace App\Geocoder\Presentation\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Запрос для поиска банка по БИК.
 */
class BankByBicRequest extends FormRequest
{
    /**
     * Определить правила валидации для запроса.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'bic' => ['required', 'string', 'digits:9'],
        ];
    }

    /**
     * Получить валидированный БИК.
     */
    public function getBic(): string
    {
        return $this->validated('bic');
    }
}
