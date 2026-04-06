<?php

declare(strict_types=1);

namespace App\Geocoder\Domain\Repositories;

use App\Geocoder\Domain\Exceptions\ExternalApiException;

/**
 * Интерфейс репозитория для работы с адресами.
 */
interface AddressRepositoryInterface
{
    /**
     * @throws ExternalApiException
     */
    public function searchAddress(string $query, ?array $locations = null): array;

    /**
     * @throws ExternalApiException
     */
    public function searchCountry(string $query): array;
}
