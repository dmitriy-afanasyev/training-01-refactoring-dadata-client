<?php

use App\Geocoder\Domain\Exceptions\GeocoderException;
use App\Geocoder\Presentation\Api\Exceptions\GeocoderExceptionHandler;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(
        function (Exceptions $exceptions): void {
            // Не логировать ValidationException — это ожидаемое поведение клиента
            $exceptions->dontReport(ValidationException::class);

            // GeocoderException логируется только через GeocoderExceptionHandler в geocoder.log
            // Без этого Laravel дублирует исключение в laravel.log
            $exceptions->dontReport(GeocoderException::class);

            // Force JSON для ValidationException — без Accept: application/json
            // Laravel рендерит HTML (редирект). Это общая логика, не привязанная к модулю.
            $exceptions->render(function (ValidationException $e): JsonResponse {
                return response()->json([
                    'message' => $e->getMessage(),
                    'errors' => $e->errors(),
                ], 422);
            });

            // Domain-исключения Geocoder (все наследуются от GeocoderException)
            $exceptions->render(function (GeocoderException $e) {
                return GeocoderExceptionHandler::handle($e);
            });

            // Fallback: все API-маршруты должны возвращать JSON
            // Без этого Laravel рендерит HTML для непредвиденных ошибок
            $exceptions->render(function (Throwable $e, Request $request) {
                if ($request->is('api/*')) {
                    return response()->json([
                        'message' => config('app.debug') ? $e->getMessage() : 'Server error',
                    ], 500);
                }

                return null;
            });
        }
    )->create();
