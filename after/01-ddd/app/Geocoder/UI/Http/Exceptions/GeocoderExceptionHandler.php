<?php

declare(strict_types=1);

namespace App\Geocoder\UI\Http\Exceptions;

use App\Geocoder\Domain\Exceptions\BankNotFoundException;
use App\Geocoder\Domain\Exceptions\ExternalApiException;
use App\Geocoder\Domain\Exceptions\GeocoderException;
use App\Geocoder\Domain\Exceptions\InvalidBicException;
use App\Geocoder\Domain\Exceptions\InvalidInnException;
use App\Geocoder\Domain\Exceptions\PartyNotFoundException;
use App\Geocoder\UI\Http\DTO\ApiResponse;
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
        BankNotFoundException::class => [
            'status' => 404,
            'error' => 'Банк не найден',
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
        return match (true) {
            $e instanceof InvalidInnException => ApiResponse::error('Неверный формат ИНН', $e->getMessage())->toResponse(),
            $e instanceof InvalidBicException => ApiResponse::error('Неверный формат БИК', $e->getMessage())->toResponse(),
            $e instanceof PartyNotFoundException => ApiResponse::notFound('Организация не найдена', $e->getMessage())->toResponse(),
            $e instanceof BankNotFoundException => ApiResponse::notFound('Банк не найден', $e->getMessage())->toResponse(),
            $e instanceof ExternalApiException => ApiResponse::badGateway('Ошибка внешнего API', $e->getMessage())->toResponse(),
            $e instanceof GeocoderException => ApiResponse::internalError('Ошибка модуля Geocoder', $e->getMessage())->toResponse(),
            default => null,
        };
    }
}
