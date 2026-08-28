<?php

declare(strict_types=1);

namespace App\Geocoder\Domain\Repositories;

use App\Geocoder\Domain\Exceptions\ExternalApiException;
use App\Geocoder\Domain\ValueObjects\Address;

interface AddressRepositoryInterface
{
    /**
     * @throws ExternalApiException
     */
    public function searchAddress(Address $address, ?array $locations = null): array;

    /**
     * @throws ExternalApiException
     */
    public function searchCountry(string $query): array;
}
