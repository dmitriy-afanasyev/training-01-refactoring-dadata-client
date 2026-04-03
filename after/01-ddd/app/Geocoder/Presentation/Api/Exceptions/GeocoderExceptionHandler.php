<?php

declare(strict_types=1);

namespace App\Geocoder\Presentation\Api\Exceptions;

use App\Geocoder\Domain\Exceptions\BankNotFoundException;
use App\Geocoder\Domain\Exceptions\ExternalApiException;
use App\Geocoder\Domain\Exceptions\GeocoderException;
use App\Geocoder\Domain\Exceptions\InvalidBicException;
use App\Geocoder\Domain\Exceptions\InvalidInnException;
use App\Geocoder\Domain\Exceptions\PartyNotFoundException;
use App\Geocoder\Presentation\Api\Responses\ApiResponseFactory;
use Closure;
use Illuminate\Http\JsonResponse;

/**
 * Обработчик исключений для модуля Geocoder.
 */
class GeocoderExceptionHandler
{
    /**
     * Маппинг исключений на фабрики ответов.
     *
     * @var array<class-string, Closure(\Throwable): JsonResponse>
     */
    private static array $exceptionHandlers;

    /**
     * Инициализировать обработчики исключений.
     */
    private static function initHandlers(): void
    {
        if (!isset(self::$exceptionHandlers)) {
            self::$exceptionHandlers = [
                InvalidInnException::class => fn(\Throwable $e) => ApiResponseFactory::error('Неверный формат ИНН', $e->getMessage(), $e->context())->toResponse(),
                InvalidBicException::class => fn(\Throwable $e) => ApiResponseFactory::error('Неверный формат БИК', $e->getMessage(), $e->context())->toResponse(),
                PartyNotFoundException::class => fn(\Throwable $e) => ApiResponseFactory::notFound('Организация не найдена', $e->getMessage(), $e->context())->toResponse(),
                BankNotFoundException::class => fn(\Throwable $e) => ApiResponseFactory::notFound('Банк не найден', $e->getMessage(), $e->context())->toResponse(),
                ExternalApiException::class => fn(\Throwable $e) => ApiResponseFactory::badGateway('Ошибка внешнего API', $e->getMessage(), $e->context())->toResponse(),
                GeocoderException::class => fn(\Throwable $e) => ApiResponseFactory::internalError('Ошибка модуля Geocoder', $e->getMessage(), $e->context())->toResponse(),
            ];
        }
    }

    /**
     * Обработать исключение и вернуть JSON-ответ.
     */
    public static function handle(\Throwable $e): ?JsonResponse
    {
        self::initHandlers();

        foreach (self::$exceptionHandlers as $exceptionClass => $handler) {
            if ($e instanceof $exceptionClass) {
                return $handler($e);
            }
        }

        return null;
    }
}
