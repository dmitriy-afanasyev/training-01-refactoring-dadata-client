<?php

declare(strict_types=1);

namespace App\Geocoder\Infrastructure\Persistence;

use App\Geocoder\Domain\Repositories\AddressRepositoryInterface;
use App\Geocoder\Domain\ValueObjects\Address;
use App\Geocoder\Infrastructure\Http\Dadata\DadataApiInterface;

/**
 * Реализация репозитория для работы с адресами через DaData API.
 */
readonly class DadataAddressRepository implements AddressRepositoryInterface
{
    public function __construct(
        private DadataApiInterface $api,
    ) {
    }

    public function search(string $query, ?array $locations = null): array
    {
        $values = $this->api->searchAddress($query, $locations);

        return array_map(
            fn(string $value): Address => Address::fromString($value),
            $values
        );
    }

    public function searchCountry(string $query): array
    {
        return $this->api->searchCountry($query);
    }
}
