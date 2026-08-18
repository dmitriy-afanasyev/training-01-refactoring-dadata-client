<?php

declare(strict_types=1);

namespace App\Geocoder\Application\Services;

use App\Geocoder\Domain\Exceptions\ExternalApiException;
use App\Geocoder\Domain\Repositories\AddressRepositoryInterface;
use App\Geocoder\Domain\ValueObjects\Address;

/*
 * DDD: Фреймворк на уровне Application.
 * В чистом DDD это недопустимо. Если фреймворк используется на уровне Application 
 * (или на уровне Domain), то это уже не DDD, а Laravel-проект с папками Application/Domain.
 * (Что для большинства легаси-проектов на самом деле неплохая альтернатива!)
 * 
 * Следует понимать, что реализовать сразу на 100% DDD невозможно — это поэтапный, итерационный процесс.
 * И сущности будут меняться (была сущность — стало VO и наоборот) по мере погружения в бизнес-процессы
 * (или изменения бизнес-процессов), и модули объединяться или разбиваться и т. д.
 * 
 * Соответственно нужны приоритеты — что важнее: более корректный единый язык, ограниченный контекст, чистота слоёв 
 * или зависимость от фреймворка (причём более лёгкая зависимость, нежели это было во времена 2000-х)?
 * 
 * Тут показан промежуточный процесс жизни DDD-проекта. 
 * В котором уделили больше внимания бизнес-процессу, но в ущерб абстракции от Laravel.
 * Как пример приоритетов и как «временное решение» в силу недостатка времени.
 * 
 * В других сервисах подобная проблема исправлена как пример «как надо». Тут оставляем как пример «как временно можно».
 * 
 * Думаешь тут нарушение SRP? см ./README.md
 */
use Illuminate\Support\Facades\Cache;

readonly class AddressService
{
    public function __construct(
        private AddressRepositoryInterface $repository,
        private int $addressCacheTtlMinutes = 1440,
        private int $countryCacheTtlMinutes = 1440,
    ) {}

    /**
     * @throws ExternalApiException
     */
    public function searchAddress(string $query, ?array $locations = null): array
    {
        $cacheKey = sprintf(
            'geocoder.address.%s.%s',
            md5($query),
            $locations ? md5(json_encode($locations, JSON_THROW_ON_ERROR)) : 'all'
        );

        return Cache::remember(
            $cacheKey,
            now()->addMinutes($this->addressCacheTtlMinutes),
            fn() => $this->searchAddresses($query, $locations)
        );
    }

    /**
     * @throws ExternalApiException
     */
    public function searchCountry(string $query): array
    {
        return Cache::remember(
            'geocoder.country.' . md5($query),
            now()->addMinutes($this->countryCacheTtlMinutes),
            fn() => $this->repository->searchCountry($query)
        );
    }

    /**
     * @param array<string, mixed>|null $locations
     * @return array<int, string>
     */
    private function searchAddresses(string $query, ?array $locations): array
    {
        $addresses = $this->repository->searchAddress($query, $locations);

        return array_map(
            fn(Address $address): string => $address->value,
            $addresses
        );
    }
}
