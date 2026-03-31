<?php

declare(strict_types=1);

namespace App\Geocoder\Providers;

use App\Geocoder\Domain\Repositories\AddressRepositoryInterface;
use App\Geocoder\Domain\Repositories\BankRepositoryInterface;
use App\Geocoder\Domain\Repositories\PartyRepositoryInterface;
use App\Geocoder\Infrastructure\Http\Dadata\DadataApiInterface;
use App\Geocoder\Infrastructure\Http\Dadata\DadataHttpClient;
use App\Geocoder\Infrastructure\Persistence\DadataAddressRepository;
use App\Geocoder\Infrastructure\Persistence\DadataBankRepository;
use App\Geocoder\Infrastructure\Persistence\DadataPartyRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Service Provider для модуля Geocoder.
 */
class GeocoderServiceProvider extends ServiceProvider
{
    /**
     * Регистрация сервисов.
     */
    public function register(): void
    {
        // Регистрируем конфигурацию
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/geocoder.php',
            'geocoder'
        );

        // Регистрируем HTTP-клиент DaData
        $this->app->bind(DadataApiInterface::class, function ($app) {
            return new DadataHttpClient(
                apiKey: config('geocoder.api_key'),
                baseUrl: config('geocoder.base_url'),
                timeout: config('geocoder.timeout', 30),
                connectTimeout: config('geocoder.connect_timeout', 10),
                retryCount: config('geocoder.retry_count', 3),
                retryDelay: config('geocoder.retry_delay', 100),
            );
        });

        // Регистрируем репозитории
        $this->app->bind(PartyRepositoryInterface::class, DadataPartyRepository::class);
        $this->app->bind(BankRepositoryInterface::class, DadataBankRepository::class);
        $this->app->bind(AddressRepositoryInterface::class, DadataAddressRepository::class);
    }

    /**
     * Загрузка сервисов.
     */
    public function boot(): void
    {
        // Публикация конфигурации
        $this->publishes([
            __DIR__ . '/../Config/geocoder.php' => config_path('geocoder.php'),
        ], 'geocoder-config');

        // Загрузка маршрутов модуля
        $this->loadRoutesFrom(__DIR__ . '/../Presentation/Http/Routes/web.php');
    }
}
