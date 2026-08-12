<?php

declare(strict_types=1);

namespace App\Geocoder\Application\Services;

use App\Geocoder\Application\Caching\CacheInterface;
use App\Geocoder\Application\DTO\PartyData;
use App\Geocoder\Domain\Exceptions\ExternalApiException;
use App\Geocoder\Domain\Exceptions\PartyNotFoundException;
use App\Geocoder\Domain\Repositories\PartyRepositoryInterface;
use App\Geocoder\Domain\ValueObjects\Inn;

readonly class PartyService
{
    public function __construct(
        private PartyRepositoryInterface $repository,
        private CacheInterface $cache,
        private int $cacheTtlMinutes = 1440,
    ) {}

    /**
     * @throws PartyNotFoundException
     * @throws ExternalApiException
     */
    public function findByInn(string $inn): PartyData
    {
        $data = $this->cache->remember(
            "geocoder.party.inn.{$inn}",
            $this->cacheTtlMinutes,
            fn () => $this->fetchPartyData($inn)
        );

        return PartyData::fromArray($data);
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchPartyData(string $inn): array
    {
        $innVO = Inn::fromString($inn);
        $party = $this->repository->findByInn($innVO);

        return $party->toArray();
    }
}
