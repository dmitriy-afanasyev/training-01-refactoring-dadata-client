<?php

declare(strict_types=1);

namespace App\Geocoder\Application\Services;

use App\Geocoder\Domain\Exceptions\ExternalApiException;
use App\Geocoder\Domain\Repositories\AddressRepositoryInterface;

/**
 * Сервис для работы с адресами.
 */
readonly class AddressService
{
    public function __construct(
        private AddressRepositoryInterface $repository,
    ) {
    }

    /**
     * Поиск адресов по запросу.
     *
     * @param string $query Запрос для поиска
     * @param array<string, mixed>|null $locations Фильтр по локациям
     *
     * @return array<int, string>
     * @throws ExternalApiException
     */
    public function search(string $query, ?array $locations = null): array
    {
        $addresses = $this->repository->search($query, $locations);

        return array_map(
            fn($address): string => $address->value,
            $addresses
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
        return $this->repository->searchCountry($query);
    }
}
