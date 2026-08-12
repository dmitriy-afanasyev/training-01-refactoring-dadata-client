<?php

declare(strict_types=1);

namespace App\Geocoder\Infrastructure\Caching;

use App\Geocoder\Application\Caching\CacheInterface;
use Illuminate\Support\Facades\Cache;

readonly class LaravelCache implements CacheInterface
{
    public function remember(string $key, int $ttlMinutes, callable $callback): mixed
    {
        return Cache::remember(
            $key,
            now()->addMinutes($ttlMinutes),
            $callback
        );
    }
}
