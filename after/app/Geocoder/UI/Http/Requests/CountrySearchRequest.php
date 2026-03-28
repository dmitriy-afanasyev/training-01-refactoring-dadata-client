<?php

declare(strict_types=1);

namespace App\Geocoder\UI\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Запрос для поиска стран.
 */
class CountrySearchRequest extends FormRequest
{
    /**
     * Определить правила валидации для запроса.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'query' => ['required', 'string', 'min:1', 'max:255'],
        ];
    }

    /**
     * Получить валидированный поисковый запрос.
     */
    public function getQuery(): string
    {
        return $this->validated('query');
    }
}
