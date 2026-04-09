<?php

declare(strict_types=1);

namespace App\Geocoder\Domain\Repositories;

use App\Geocoder\Domain\Entities\Party;
use App\Geocoder\Domain\Exceptions\ExternalApiException;
use App\Geocoder\Domain\Exceptions\PartyNotFoundException;
use App\Geocoder\Domain\ValueObjects\Inn;

interface PartyRepositoryInterface
{
    /**
     * @throws PartyNotFoundException
     * @throws ExternalApiException
     */
    public function findByInn(Inn $inn): Party;
}
