<?php

declare(strict_types=1);

namespace App\Geocoder\Domain\Exceptions;

/**
 * Исключение ошибки внешнего API.
 */
class ExternalApiException extends GeocoderException
{
    public function __construct(
        string $message,
        private int $httpStatus = 0,
    ) {
        parent::__construct($message);
    }

    /**
     * Получить контекст исключения для логирования.
     */
    public function context(): array
    {
        return [
            'http_status' => $this->httpStatus,
        ];
    }
}
