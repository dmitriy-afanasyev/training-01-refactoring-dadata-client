<?php

declare(strict_types=1);

namespace App\Geocoder\Infrastructure\Persistence;

use App\Geocoder\Domain\Entities\Bank;
use App\Geocoder\Domain\Exceptions\BankNotFoundException;
use App\Geocoder\Domain\Exceptions\ExternalApiException;
use App\Geocoder\Domain\Repositories\BankRepositoryInterface;
use App\Geocoder\Domain\ValueObjects\Bic;
use App\Geocoder\Domain\ValueObjects\Inn;
use App\Geocoder\Infrastructure\Http\Dadata\DadataApiInterface;

/**
 * Реализация репозитория для работы с банками через DaData API.
 */
readonly class DadataBankRepository implements BankRepositoryInterface
{
    public function __construct(
        private DadataApiInterface $api,
    ) {
    }

    public function findByBicOrFail(Bic $bic): Bank
    {
        $data = $this->api->findBankByBic($bic->value);

        if ($data === null) {
            throw new BankNotFoundException(sprintf('Банк с БИК %s не найден', $bic->value));
        }

        return $this->mapToBank($data);
    }

    /**
     * Маппинг данных API в сущность Bank.
     *
     * @param array<string, mixed> $data
     */
    private function mapToBank(array $data): Bank
    {
        $bic = Bic::fromString($data['bic'] ?? '');

        return new Bank(
            id: $bic,
            name: $data['name']['full'] ?? '',
            shortName: $data['name']['short'] ?? '',
            bic: $bic,
            inn: Inn::fromString($data['inn'] ?? ''),
            correspondentAccount: $data['correspondent_account'] ?? null,
            address: $data['address']['value'] ?? null,
            status: $data['state']['status'] ?? null,
        );
    }
}
