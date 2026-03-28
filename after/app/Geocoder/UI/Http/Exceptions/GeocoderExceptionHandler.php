<?php

declare(strict_types=1);

namespace App\Geocoder\UI\Http\Exceptions;

use App\Geocoder\Domain\Exceptions\ExternalApiException;
use App\Geocoder\Domain\Exceptions\GeocoderException;
use App\Geocoder\Domain\Exceptions\InvalidBicException;
use App\Geocoder\Domain\Exceptions\InvalidInnException;
use App\Geocoder\Domain\Exceptions\PartyNotFoundException;
use Illuminate\Http\JsonResponse;

/**
 * Обработчик исключений для модуля Geocoder.
 */
class GeocoderExceptionHandler
{
    /**
     * Маппинг исключений на HTTP-статусы и сообщения.
     *
     * @var array<class-string, array{status: int, error: string}>
     */
    private const EXCEPTION_MAP = [
        InvalidInnException::class => [
            'status' => 400,
            'error' => 'Неверный формат ИНН',
        ],
        InvalidBicException::class => [
            'status' => 400,
            'error' => 'Неверный формат БИК',
        ],
        PartyNotFoundException::class => [
            'status' => 404,
            'error' => 'Организация не найдена',
        ],
        ExternalApiException::class => [
            'status' => 502,
            'error' => 'Ошибка внешнего API',
        ],
        GeocoderException::class => [
            'status' => 500,
            'error' => 'Ошибка модуля Geocoder',
        ],
    ];

    /**
     * Обработать исключение и вернуть JSON-ответ.
     */
    public static function handle(\Throwable $e): ?JsonResponse
    {
        foreach (self::EXCEPTION_MAP as $exceptionClass => $config) {
            if ($e instanceof $exceptionClass) {
                return response()->json([
                    'success' => false,
                    'error' => $config['error'],
                    'message' => $e->getMessage(),
                ], $config['status']);
            }
        }

        return null;
    }
}
