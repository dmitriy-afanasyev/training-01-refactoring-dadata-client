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
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

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
                InvalidInnException::class => function (InvalidInnException $e): JsonResponse {
                    $context = config('app.debug') ? $e->context() : [];

                    return self::logAndRespond($e, ApiResponseFactory::error('Неверный формат ИНН', $e->getMessage(), $context));
                },
                InvalidBicException::class => function (InvalidBicException $e): JsonResponse {
                    $context = config('app.debug') ? $e->context() : [];

                    return self::logAndRespond($e, ApiResponseFactory::error('Неверный формат БИК', $e->getMessage(), $context));
                },
                PartyNotFoundException::class => function (PartyNotFoundException $e): JsonResponse {
                    $context = config('app.debug') ? $e->context() : [];

                    return self::logAndRespond($e, ApiResponseFactory::notFound('Организация не найдена', $e->getMessage(), $context));
                },
                BankNotFoundException::class => function (BankNotFoundException $e): JsonResponse {
                    $context = config('app.debug') ? $e->context() : [];

                    return self::logAndRespond($e, ApiResponseFactory::notFound('Банк не найден', $e->getMessage(), $context));
                },
                ExternalApiException::class => function (ExternalApiException $e): JsonResponse {
                    $context = config('app.debug') ? $e->context() : [];

                    return self::logAndRespond($e, ApiResponseFactory::badGateway('Ошибка внешнего API', $e->getMessage(), $context));
                },
                GeocoderException::class => function (GeocoderException $e): JsonResponse {
                    $context = config('app.debug') ? $e->context() : [];

                    return self::logAndRespond($e, ApiResponseFactory::internalError('Ошибка модуля Geocoder', $e->getMessage(), $context));
                },
            ];
        }
    }

    private static function logAndRespond(\Throwable $e, ApiResponseFactory $response): JsonResponse
    {
        $context = [
            'request' => [
                'url' => Request::fullUrl(),
                'method' => Request::method(),
                'input' => Request::except(['password', 'token', 'api_key']),
                'ip' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ],
            'source' => [
                'route_action' => Request::route()?->getActionName(),
            ],
            'exception' => [
                'class' => get_class($e),
                'message' => $e->getMessage(),
            ] + (config('app.debug') ? [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ] : []),
        ];

        // Логировать контекст исключения только в debug-режиме
        // В production чувствительные данные (raw API response) не попадают в логи
        if ($e instanceof GeocoderException && config('app.debug')) {
            $context['exception']['context'] = $e->context();
        }

        if (app()->environment('testing')) {
            return $response->toResponse();
        }

        $channel = $e instanceof GeocoderException ? self::LOG_CHANNEL : null;
        Log::channel($channel)->error($e->getMessage(), $context);

        return $response->toResponse();
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
