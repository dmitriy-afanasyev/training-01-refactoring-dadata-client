<?php

declare(strict_types=1);

namespace App\Geocoder\Application\Caching;

/**
 * Outbound port для кэширования на прикладном уровне.
 * Application решает, кешировать ли сценарий, по какому ключу и на сколько;
 * Infrastructure (LaravelCache) реализует, как именно хранить данные.
 */
interface CacheInterface
{
    /**
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    public function remember(string $key, int $ttlMinutes, callable $callback): mixed;
}
