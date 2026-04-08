<?php

declare(strict_types=1);

namespace App\Geocoder\Infrastructure\Persistence;

use App\Geocoder\Domain\Enums\BankStatus;
use App\Geocoder\Domain\Entities\Bank;
use App\Geocoder\Domain\Exceptions\BankNotFoundException;
use App\Geocoder\Domain\Repositories\BankRepositoryInterface;
use App\Geocoder\Domain\ValueObjects\Bic;
use App\Geocoder\Domain\ValueObjects\Inn;
use App\Geocoder\Infrastructure\Http\Dadata\DadataApiInterface;

readonly class DadataBankRepository implements BankRepositoryInterface
{
    public function __construct(
        private DadataApiInterface $api,
    ) {}

    public function findByBicOrFail(Bic $bic): Bank
    {
        $data = $this->api->findBankByBic($bic->value);

        if ($data === null) {
            throw new BankNotFoundException($bic->value);
        }

        return $this->mapToBank($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function mapToBank(array $data): Bank
    {
        $nameData = $data['name'] ?? [];

        return new Bank(
            id: Bic::fromString($data['bic']),
            name: $nameData['full'] ?? null,
            shortName: $nameData['short'] ?? null,
            bic: Bic::fromString($data['bic']),
            inn: Inn::fromString($data['inn'] ?? ''),
            correspondentAccount: $data['correspondent_account'] ?? null,
            address: $data['address']['value'] ?? null,
            status: BankStatus::fromString($data['state']['status'] ?? null),
        );
    }
}
