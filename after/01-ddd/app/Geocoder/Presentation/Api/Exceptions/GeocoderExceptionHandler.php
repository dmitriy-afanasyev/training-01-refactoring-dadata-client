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
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

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

    private static function initHandlers(): void
    {
        if (!isset(self::$exceptionHandlers)) {
            self::$exceptionHandlers = [
                ValidationException::class => fn(\Throwable $e) => self::logAndRespond($e, ApiResponseFactory::validationError('Ошибка валидации', $e->errors())),
                InvalidInnException::class => fn(\Throwable $e) => self::logAndRespond($e, ApiResponseFactory::error('Неверный формат ИНН', $e->getMessage(), $e->context())),
                InvalidBicException::class => fn(\Throwable $e) => self::logAndRespond($e, ApiResponseFactory::error('Неверный формат БИК', $e->getMessage(), $e->context())),
                PartyNotFoundException::class => fn(\Throwable $e) => self::logAndRespond($e, ApiResponseFactory::notFound('Организация не найдена', $e->getMessage(), $e->context())),
                BankNotFoundException::class => fn(\Throwable $e) => self::logAndRespond($e, ApiResponseFactory::notFound('Банк не найден', $e->getMessage(), $e->context())),
                ExternalApiException::class => fn(\Throwable $e) => self::logAndRespond($e, ApiResponseFactory::badGateway('Ошибка внешнего API', $e->getMessage(), $e->context())),
                GeocoderException::class => fn(\Throwable $e) => self::logAndRespond($e, ApiResponseFactory::internalError('Ошибка модуля Geocoder', $e->getMessage(), $e->context())),
            ];
        }
    }

    private static function logAndRespond(\Throwable $e, ApiResponseFactory $response): JsonResponse
    {
        Log::error($e->getMessage(), [
            'exception' => get_class($e),
            'context' => $e instanceof GeocoderException ? $e->context() : [],
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        return $response->toResponse();
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
