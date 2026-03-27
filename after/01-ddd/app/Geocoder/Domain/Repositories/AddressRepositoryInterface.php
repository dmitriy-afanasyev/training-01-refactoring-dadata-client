<?php

declare(strict_types=1);

namespace App\Geocoder\Domain\Repositories;

use App\Geocoder\Domain\Entities\Address;
use App\Geocoder\Domain\Exceptions\ExternalApiException;

/**
 * Интерфейс репозитория для работы с адресами.
 */
interface AddressRepositoryInterface
{
    /**
     * Поиск адресов по запросу.
     *
     * @param string $query Запрос для поиска
     * @param array<string, mixed>|null $locations Фильтр по локациям
     *
     * @return array<int, Address>
     *
     * @throws ExternalApiException
     */
    public function search(string $query, ?array $locations = null): array;

    /**
     * Поиск стран по запросу.
     *
     * @param string $query Запрос для поиска
     *
     * @return array<int, string>
     *
     * @throws ExternalApiException
     */
    public function searchCountry(string $query): array;
}
