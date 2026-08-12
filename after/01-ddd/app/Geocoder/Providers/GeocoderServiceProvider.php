<?php

declare(strict_types=1);

namespace App\Geocoder\Providers;

use App\Geocoder\Application\Caching\CacheInterface;
use App\Geocoder\Application\Services\AddressService;
use App\Geocoder\Application\Services\BankService;
use App\Geocoder\Application\Services\PartyService;
use App\Geocoder\Domain\Repositories\AddressRepositoryInterface;
use App\Geocoder\Domain\Repositories\BankRepositoryInterface;
use App\Geocoder\Domain\Repositories\PartyRepositoryInterface;
use App\Geocoder\Infrastructure\Caching\LaravelCache;
use App\Geocoder\Infrastructure\Http\Dadata\DadataApiInterface;
use App\Geocoder\Infrastructure\Http\Dadata\DadataHttpClient;
use App\Geocoder\Infrastructure\Persistence\DadataAddressRepository;
use App\Geocoder\Infrastructure\Persistence\DadataBankRepository;
use App\Geocoder\Infrastructure\Persistence\DadataPartyRepository;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

/**
 * Composition root — единственное место где допустим config().
 * Правило: DI over app()/resolve() в слоях (architecture → Use Dependency Injection).
 */
class GeocoderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../Config/geocoder.php',
            'geocoder'
        );

        $this->app->bind(DadataApiInterface::class, function ($app) {
            return new DadataHttpClient(
                apiKey: config('geocoder.api_key', ''),
                baseUrl: config('geocoder.base_url'),
                timeout: config('geocoder.timeout', 40),
                connectTimeout: config('geocoder.connect_timeout', 20),
                retryCount: config('geocoder.retry_count', 3),
                retryDelay: config('geocoder.retry_delay', 100),
                maxRedirects: config('geocoder.max_redirects', 10),
                interface: config('app.external_ip'),
            );
        });

        $this->app->bind(PartyRepositoryInterface::class, DadataPartyRepository::class);
        $this->app->bind(BankRepositoryInterface::class, DadataBankRepository::class);
        $this->app->bind(AddressRepositoryInterface::class, DadataAddressRepository::class);

        $this->app->bind(CacheInterface::class, LaravelCache::class);

        $this->app->when(BankService::class)->needs('$cacheTtlMinutes')->give(fn () => config('geocoder.cache.bank_ttl_minutes'));
        $this->app->when(PartyService::class)->needs('$cacheTtlMinutes')->give(fn () => config('geocoder.cache.party_ttl_minutes'));
        $this->app->when(AddressService::class)->needs('$addressCacheTtlMinutes')->give(fn () => config('geocoder.cache.address_ttl_minutes'));
        $this->app->when(AddressService::class)->needs('$countryCacheTtlMinutes')->give(fn () => config('geocoder.cache.country_ttl_minutes'));
    }

    public function boot(): void
    {
        $this->registerRateLimiter();
        $this->loadRoutesFrom(__DIR__.'/../Presentation/Api/Routes/api.php');
    }

    private function registerRateLimiter(): void
    {
        RateLimiter::for('geocoder', function () {
            return Limit::perMinutes(
                config('geocoder.throttle.decay_minutes', 1),
                config('geocoder.throttle.max_attempts', 100)
            );
        });
    }
}
