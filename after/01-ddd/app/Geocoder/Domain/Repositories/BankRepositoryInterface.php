<?php

declare(strict_types=1);

namespace App\Geocoder\Domain\Repositories;

use App\Geocoder\Domain\Entities\Bank;
use App\Geocoder\Domain\Exceptions\BankNotFoundException;
use App\Geocoder\Domain\Exceptions\ExternalApiException;
use App\Geocoder\Domain\ValueObjects\Bic;

interface BankRepositoryInterface
{
    /**
     * @throws BankNotFoundException
     * @throws ExternalApiException
     */
    public function findByBicOrFail(Bic $bic): Bank;
}
