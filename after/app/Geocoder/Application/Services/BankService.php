<?php

declare(strict_types=1);

namespace App\Geocoder\Application\Services;

use App\Geocoder\Application\DTO\BankData;
use App\Geocoder\Domain\Exceptions\ExternalApiException;
use App\Geocoder\Domain\Exceptions\InvalidBicException;
use App\Geocoder\Domain\Repositories\BankRepositoryInterface;
use App\Geocoder\Domain\ValueObjects\Bic;

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
     * @throws ExternalApiException
     */
    public function findByBic(string $bic): ?BankData
    {
        $bicVO = Bic::fromString($bic);

        $bank = $this->repository->findByBic($bicVO);

        if ($bank === null) {
            return null;
        }

        return BankData::fromArray($bank->toArray());
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
}
