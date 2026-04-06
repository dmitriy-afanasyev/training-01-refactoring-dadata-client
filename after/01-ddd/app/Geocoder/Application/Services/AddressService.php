<?php

declare(strict_types=1);

namespace App\Geocoder\Application\Services;

use App\Geocoder\Domain\Exceptions\ExternalApiException;
use App\Geocoder\Domain\Repositories\AddressRepositoryInterface;
use App\Geocoder\Domain\ValueObjects\Address;
use Illuminate\Support\Facades\Cache;

/**
 * Сервис для работы с адресами.
 */
readonly class AddressService
{
    public function __construct(
        // DDD: Слой приложения может обращаться к возможностям слоя Инфраструктуры, 
        //  но через интерфейсы Доменного слоя
        private AddressRepositoryInterface $repository,
    ) {}

    /**
     * Поиск адресов по запросу.
     *
     * @param string $query Запрос для поиска
     * @param array<string, mixed>|null $locations Фильтр по локациям
     *
     * @return array<int, string>
     * @throws ExternalApiException
     */
    public function searchAddress(string $query, ?array $locations = null): array
    {
        $cacheKey = sprintf(
            'geocoder.address.%s.%s',
            md5($query),
            $locations ? md5(serialize($locations)) : 'all'
        );

        // DDD: Кэшировать (и на сколько) или нет - это логика уровня приложения, а не домена.
        //  По этому кэшируем в этом слое и в сервисе, а не в слое домена.
        //  Например поиск адреса можно делать для клиента магазина, и тогда можно кешировать на долго,
        //  а можно искать адрес в админке, тогда надо кэшировать на пару минут.
        return Cache::remember(
            $cacheKey,
            now()->addHours(24),
            fn() => $this->searchAddresses($query, $locations)
        );
    }

    /**
     * Поиск стран по запросу.
     *
     * @param string $query Запрос для поиска
     *
     * @return array<int, string>
     * @throws ExternalApiException
     */
    public function searchCountry(string $query): array
    {
        return Cache::remember(
            "geocoder.country.{$query}",
            now()->addHours(24),
            fn() => $this->repository->searchCountry($query)
        );
    }

    /**
     * Поиск адресов.
     *
     * @param array<string, mixed>|null $locations
     * @return array<int, string>
     */
    private function searchAddresses(string $query, ?array $locations): array
    {
        $addresses = $this->repository->searchAddress($query, $locations);

        // DDD: данные будут переданы в слой Представления и там не должно быть зависимостей от слоя Домена.
        //  По этому трансформируем ответ либо в DTO текущего слоя (приложения) либо в примитив.
        return array_map(
            fn(Address $address): string => $address->value,
            $addresses
        );
    }
}
