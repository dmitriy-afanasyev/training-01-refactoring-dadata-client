<?php

declare(strict_types=1);

namespace App\Geocoder\Domain\Repositories;

use App\Geocoder\Domain\Entities\Party;
use App\Geocoder\Domain\Exceptions\ExternalApiException;
use App\Geocoder\Domain\Exceptions\PartyNotFoundException;
use App\Geocoder\Domain\ValueObjects\Inn;

/**
 * Интерфейс репозитория для работы с данными организаций.
 */
interface PartyRepositoryInterface
{
    /**
     * Найти организацию по ИНН.
     *
     * @throws PartyNotFoundException
     * @throws ExternalApiException
     */
    public function findByInn(Inn $inn): ?Party;
}
