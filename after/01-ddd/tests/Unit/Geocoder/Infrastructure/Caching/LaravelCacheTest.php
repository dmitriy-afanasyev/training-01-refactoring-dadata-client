<?php

declare(strict_types=1);

namespace Tests\Unit\Geocoder\Infrastructure\Caching;

use App\Geocoder\Infrastructure\Caching\LaravelCache;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(LaravelCache::class)]
class LaravelCacheTest extends TestCase
{
    public function test_remember_delegates_to_laravel_cache_and_returns_callback_result(): void
    {
        $key = 'geocoder.bank.bic.044525225';
        $ttlMinutes = 1440;
        $expected = ['bic' => '044525225'];

        Cache::shouldReceive('remember')
            ->once()
            ->with($key, static::isInstanceOf(\DateTimeInterface::class), static::isCallable())
            ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

        $cache = new LaravelCache;

        $this->assertSame($expected, $cache->remember($key, $ttlMinutes, fn () => $expected));
    }
}
