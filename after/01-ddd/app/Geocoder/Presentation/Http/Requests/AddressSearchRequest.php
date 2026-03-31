<?php

declare(strict_types=1);

namespace App\Geocoder\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Запрос для поиска адресов.
 */
class AddressSearchRequest extends FormRequest
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
            'locations' => ['nullable', 'array'],
        ];
    }

    /**
     * Получить валидированный поисковый запрос.
     */
    public function getQuery(): string
    {
        return $this->validated('query');
    }

    /**
     * Получить валидированные локации.
     *
     * @return array<string, mixed>|null
     */
    public function getLocations(): ?array
    {
        return $this->validated('locations');
    }
}
