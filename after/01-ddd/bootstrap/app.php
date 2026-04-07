<?php

use App\Geocoder\Providers\GeocoderServiceProvider;
use App\Geocoder\Presentation\Api\Exceptions\GeocoderExceptionHandler;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

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
        $middleware->appendToGroup('geocoder.throttle', [
            sprintf(
                'throttle:%d,%d',
                config('geocoder.throttle.max_attempts', 100),
                config('geocoder.throttle.decay_minutes', 1)
            ),
        ]);
    })
    ->withExceptions(
        function (Exceptions $exceptions): void {
            $exceptions->render(function (\Throwable $e) {
                return GeocoderExceptionHandler::handle($e);
            });
        }
    )->create();
