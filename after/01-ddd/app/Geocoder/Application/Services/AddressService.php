<?php

declare(strict_types=1);

namespace App\Geocoder\Application\Services;

use App\Geocoder\Domain\Exceptions\ExternalApiException;
use App\Geocoder\Domain\Repositories\AddressRepositoryInterface;
use App\Geocoder\Domain\ValueObjects\Address;
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
