<?php

use App\Geocoder\Providers\GeocoderServiceProvider;
use App\Geocoder\Presentation\Api\Exceptions\GeocoderExceptionHandler;
use App\Geocoder\Presentation\Api\Responses\ApiResponseFactory;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
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
            // Force JSON для ValidationException на всех API маршрутах
            // Без Accept: application/json Laravel рендерит HTML (редирект)
            $exceptions->render(function (ValidationException $e) {
                return ApiResponseFactory::validationError('Ошибка валидации', $e->errors())->toResponse();
            });

            // Domain-исключения Geocoder
            $exceptions->render(function (\Throwable $e) {
                return GeocoderExceptionHandler::handle($e);
            });
        }
    )->create();
