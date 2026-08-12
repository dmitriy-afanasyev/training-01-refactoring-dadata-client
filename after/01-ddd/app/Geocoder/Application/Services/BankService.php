<?php

declare(strict_types=1);

namespace App\Geocoder\Application\Services;

use App\Geocoder\Application\Caching\CacheInterface;
use App\Geocoder\Application\DTO\BankData;
use App\Geocoder\Domain\Exceptions\BankNotFoundException;
use App\Geocoder\Domain\Exceptions\ExternalApiException;
use App\Geocoder\Domain\Repositories\BankRepositoryInterface;
use App\Geocoder\Domain\ValueObjects\Bic;

readonly class BankService
{
    public function __construct(
        private BankRepositoryInterface $repository,
        private CacheInterface $cache,
        private int $cacheTtlMinutes = 1440,
    ) {}

    /**
     * @throws BankNotFoundException
     * @throws ExternalApiException
     */
    public function findByBic(string $bic): BankData
    {
        $data = $this->cache->remember(
            "geocoder.bank.bic.{$bic}",
            $this->cacheTtlMinutes,
            fn () => $this->fetchBankData($bic)
        );

        return BankData::fromArray($data);
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchBankData(string $bic): array
    {
        $bank = $this->repository->findByBicOrFail(
            Bic::fromString($bic)
        );

        return $bank->toArray();
    }
}
