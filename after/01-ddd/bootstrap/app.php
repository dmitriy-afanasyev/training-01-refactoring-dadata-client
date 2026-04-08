<?php

use App\Geocoder\Providers\GeocoderServiceProvider;
use App\Geocoder\Presentation\Api\Exceptions\GeocoderExceptionHandler;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withProviders([
        GeocoderServiceProvider::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(
        function (Exceptions $exceptions): void {
            // Не логировать ValidationException — это ожидаемое поведение клиента
            $exceptions->dontReport(ValidationException::class);

            // Force JSON для ValidationException — без Accept: application/json
            // Laravel рендерит HTML (редирект). Это общая логика, не привязанная к модулю.
            $exceptions->render(function (ValidationException $e): JsonResponse {
                return response()->json([
                    'message' => $e->getMessage(),
                    'errors' => $e->errors(),
                ], 422);
            });

            // Domain-исключения Geocoder
            $exceptions->render(function (\Throwable $e) {
                return GeocoderExceptionHandler::handle($e);
            });
        }
    )->create();
