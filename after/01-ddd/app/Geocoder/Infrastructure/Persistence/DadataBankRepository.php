<?php

declare(strict_types=1);

namespace App\Geocoder\Infrastructure\Persistence;

use App\Geocoder\Domain\Enums\BankStatus;
use App\Geocoder\Domain\Entities\Bank;
use App\Geocoder\Domain\Exceptions\BankNotFoundException;
use App\Geocoder\Domain\Exceptions\ExternalApiException;
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
        $fullName = $nameData['full'] ?? $nameData['full_with_opf'] ?? $nameData['short'] ?? '';
        $shortName = $nameData['short'] ?? $nameData['short_with_opf'] ?? $fullName;

        if ($fullName === '') {
            throw new ExternalApiException(
                'DaData API returned bank without a name',
                response: $data
            );
        }

        $bic = Bic::fromString($data['bic'] ?? '');

        return new Bank(
            id: $bic,
            name: $fullName,
            shortName: $shortName,
            bic: $bic,
            inn: Inn::fromString($data['inn'] ?? ''),
            correspondentAccount: $data['correspondent_account'] ?? null,
            address: $data['address']['value'] ?? null,
            status: BankStatus::fromString($data['state']['status'] ?? null),
        );
    }
}
