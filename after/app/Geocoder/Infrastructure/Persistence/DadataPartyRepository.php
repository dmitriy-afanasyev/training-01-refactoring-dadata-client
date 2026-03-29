<?php

declare(strict_types=1);

namespace App\Geocoder\Infrastructure\Persistence;

use App\Geocoder\Domain\Entities\Party;
use App\Geocoder\Domain\Exceptions\PartyNotFoundException;
use App\Geocoder\Domain\Repositories\PartyRepositoryInterface;
use App\Geocoder\Domain\ValueObjects\Inn;
use App\Geocoder\Infrastructure\Http\Dadata\DadataApiInterface;

/**
 * Реализация репозитория для работы с организациями через DaData API.
 */
readonly class DadataPartyRepository implements PartyRepositoryInterface
{
    public function __construct(
        private DadataApiInterface $api,
    ) {
    }

    public function findByInn(Inn $inn): ?Party
    {
        $data = $this->api->findPartyByInn($inn->value);

        if ($data === null) {
            throw new PartyNotFoundException(
                sprintf('Организация с ИНН %s не найдена', $inn->value)
            );
        }

        return $this->mapToParty($data);
    }

    /**
     * Маппинг данных API в сущность Party.
     *
     * @param array<string, mixed> $data
     */
    private function mapToParty(array $data): Party
    {
        return new Party(
            name: $data['name']['full_with_opf'] ?? $data['name']['short_with_opf'] ?? '',
            shortName: $data['name']['short_with_opf'] ?? $data['name']['full_with_opf'] ?? '',
            inn: Inn::fromString($data['inn'] ?? ''),
            kpp: $data['kpp'] ?? null,
            ogrn: $data['ogrn'] ?? null,
            okpo: $data['okpo'] ?? null,
            address: $data['address']['value'] ?? null,
            status: $data['state']['status'] ?? null,
        );
    }
}
