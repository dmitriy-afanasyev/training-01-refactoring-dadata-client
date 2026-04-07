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
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\ValidationException;

/**
 * Обработчик исключений для модуля Geocoder.
 */
class GeocoderExceptionHandler
{
    public const LOG_CHANNEL = 'geocoder';

    /**
     * Маппинг исключений на фабрики ответов.
     *
     * @var array<class-string, callable(\Throwable): JsonResponse>
     */
    private static array $exceptionHandlers;

    private static function initHandlers(): void
    {
        if (!isset(self::$exceptionHandlers)) {
            self::$exceptionHandlers = [
                ValidationException::class => function (ValidationException $e): JsonResponse {
                    return self::logAndRespond($e, ApiResponseFactory::validationError('Ошибка валидации', $e->errors()));
                },
                InvalidInnException::class => function (InvalidInnException $e): JsonResponse {
                    return self::logAndRespond($e, ApiResponseFactory::error('Неверный формат ИНН', $e->getMessage(), $e->context()));
                },
                InvalidBicException::class => function (InvalidBicException $e): JsonResponse {
                    return self::logAndRespond($e, ApiResponseFactory::error('Неверный формат БИК', $e->getMessage(), $e->context()));
                },
                PartyNotFoundException::class => function (PartyNotFoundException $e): JsonResponse {
                    return self::logAndRespond($e, ApiResponseFactory::notFound('Организация не найдена', $e->getMessage(), $e->context()));
                },
                BankNotFoundException::class => function (BankNotFoundException $e): JsonResponse {
                    return self::logAndRespond($e, ApiResponseFactory::notFound('Банк не найден', $e->getMessage(), $e->context()));
                },
                ExternalApiException::class => function (ExternalApiException $e): JsonResponse {
                    return self::logAndRespond($e, ApiResponseFactory::badGateway('Ошибка внешнего API', $e->getMessage(), $e->context()));
                },
                GeocoderException::class => function (GeocoderException $e): JsonResponse {
                    return self::logAndRespond($e, ApiResponseFactory::internalError('Ошибка модуля Geocoder', $e->getMessage(), $e->context()));
                },
            ];
        }
    }

    private static function logAndRespond(\Throwable $e, ApiResponseFactory $response): JsonResponse
    {
        $sourceClass = self::findSourceClass($e);

        $context = [
            'request' => [
                'url' => Request::fullUrl(),
                'method' => Request::method(),
                'input' => Request::all(),
                'ip' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ],
            'source' => [
                'class' => $sourceClass,
                'route_action' => Request::route()?->getActionName(),
            ],
            'exception' => [
                'class' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ],
        ];

        if ($e instanceof GeocoderException) {
            $context['exception']['context'] = $e->context();
        }

        if ($e instanceof ValidationException) {
            $context['validation_errors'] = $e->errors();
        }

        $channel = $e instanceof GeocoderException ? self::LOG_CHANNEL : null;
        Log::channel($channel)->error($e->getMessage(), $context);

        return $response->toResponse();
    }

    /**
     * Найти первый кадр из нашего кода в трейсе исключения.
     */
    private static function findSourceClass(\Throwable $e): ?string
    {
        foreach ($e->getTrace() as $frame) {
            if (!isset($frame['class'])) {
                continue;
            }

            $class = $frame['class'];

            if (str_starts_with($class, 'App\\') && !str_starts_with($class, 'App\\Providers\\')) {
                return $class;
            }
        }

        return null;
    }

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
