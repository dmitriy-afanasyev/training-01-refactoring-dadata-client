<?php

declare(strict_types=1);

namespace App\Geocoder\Application\Services;

use App\Geocoder\Application\DTO\PartyData;
use App\Geocoder\Domain\Exceptions\ExternalApiException;
use App\Geocoder\Domain\Exceptions\PartyNotFoundException;
use App\Geocoder\Domain\Repositories\PartyRepositoryInterface;
use App\Geocoder\Domain\ValueObjects\Inn;
use Illuminate\Support\Facades\Cache;

/**
 * Сервис для работы с данными организаций.
 */
readonly class PartyService
{
    public function __construct(
        private PartyRepositoryInterface $repository,
    ) {}

    /**
     * Найти организацию по ИНН.
     * @see Dadata\DaDataRu::getCompanyDataByInn()
     * 
     * @param string $inn
     * @return PartyData
     * @throws PartyNotFoundException
     * @throws ExternalApiException
     */
    public function findByInn(string $inn): PartyData
    {
        $data = Cache::remember(
            "geocoder.party.inn.{$inn}",
            now()->addHours(24),
            fn() => $this->fetchPartyData($inn)
        );

        return PartyData::fromArray($data);
    }

    /**
     * Получить данные организации из репозитория.
     *
     * @return array<string, mixed>
     */
    private function fetchPartyData(string $inn): array
    {
        $innVO = Inn::fromString($inn);
        $party = $this->repository->findByInn($innVO);

        return $party->toArray();
    }
}
