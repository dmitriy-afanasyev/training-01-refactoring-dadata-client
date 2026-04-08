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
                InvalidInnException::class => fn($e) => self::respond($e, ApiResponseFactory::error('Неверный формат ИНН')),
                InvalidBicException::class => fn($e) => self::respond($e, ApiResponseFactory::error('Неверный формат БИК')),
                PartyNotFoundException::class => fn($e) => self::respond($e, ApiResponseFactory::notFound('Организация не найдена')),
                BankNotFoundException::class => fn($e) => self::respond($e, ApiResponseFactory::notFound('Банк не найден')),
                ExternalApiException::class => fn($e) => self::respond($e, ApiResponseFactory::badGateway('Ошибка внешнего API')),
                GeocoderException::class => fn($e) => self::respond($e, ApiResponseFactory::internalError('Ошибка модуля Geocoder')),
            ];
        }
    }

    private static function respond(\Throwable $e, ApiResponseFactory $response): JsonResponse
    {
        $debugContext = config('app.debug') && $e instanceof GeocoderException
            ? $e->context()
            : [];

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
                'context' => $debugContext,
            ] : []),
        ];

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
