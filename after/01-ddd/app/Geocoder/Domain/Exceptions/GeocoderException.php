<?php

declare(strict_types=1);

namespace App\Geocoder\Domain\Exceptions;

use Exception;

/**
 * Базовое исключение для модуля Geocoder (Domain Layer).
 */
class GeocoderException extends Exception
{
    /**
     * Получить дополнительный контекст для логирования.
     *
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return [];
    }
}
