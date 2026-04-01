<?php

declare(strict_types=1);

namespace App\Geocoder\Presentation\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Запрос для поиска организации по ИНН.
 */
class PartyByInnRequest extends FormRequest
{
    /**
     * Определить правила валидации для запроса.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'inn' => ['required', 'string', 'digits_between:10,12'],
        ];
    }

    /**
     * Получить валидированный ИНН.
     */
    public function getInn(): string
    {
        return $this->validated('inn');
    }
}
