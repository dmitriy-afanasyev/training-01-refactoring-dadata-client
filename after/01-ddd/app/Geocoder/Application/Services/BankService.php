<?php

declare(strict_types=1);

namespace App\Geocoder\Application\Services;

use App\Geocoder\Application\DTO\BankData;
use App\Geocoder\Domain\Exceptions\BankNotFoundException;
use App\Geocoder\Domain\Exceptions\ExternalApiException;
use App\Geocoder\Domain\Exceptions\InvalidBicException;
use App\Geocoder\Domain\Repositories\BankRepositoryInterface;
use App\Geocoder\Domain\ValueObjects\Bic;
use Illuminate\Support\Facades\Cache;

/**
 * Сервис для работы с данными банков.
 */
readonly class BankService
{
    public function __construct(
        private BankRepositoryInterface $repository,
    ) {
    }

    /**
     * Найти банк по БИК.
     *
     * @throws BankNotFoundException
     * @throws ExternalApiException
     */
    public function findByBic(string $bic): BankData
    {
        return Cache::remember(
            "geocoder.bank.bic.{$bic}",
            now()->addHours(24),
            fn() => $this->findBankData($bic)
        );
    }

    /**
     * Проверить валидность БИК.
     */
    public function validateBic(string $bic): bool
    {
        try {
            Bic::fromString($bic);
            return true;
        } catch (InvalidBicException) {
            return false;
        }
    }

    /**
     * Найти данные банка.
     */
    private function findBankData(string $bic): BankData
    {
        $bank = $this->repository->findByBicOrFail(
            Bic::fromString($bic)
        );

        return BankData::fromArray($bank->toArray());
    }
}
