<?php

declare(strict_types=1);

namespace App\Geocoder\Domain\Repositories;

use App\Geocoder\Domain\Entities\Bank;
use App\Geocoder\Domain\Exceptions\ExternalApiException;
use App\Geocoder\Domain\ValueObjects\Bic;

/**
 * Интерфейс репозитория для работы с данными банков.
 */
interface BankRepositoryInterface
{
    /**
     * Найти банк по БИК.
     *
     * @throws ExternalApiException
     */
    public function findByBic(Bic $bic): ?Bank;

    /**
     * Найти банки по названию.
     *
     * @return array<int, Bank>
     *
     * @throws ExternalApiException
     */
    public function searchByName(string $name): array;
}
