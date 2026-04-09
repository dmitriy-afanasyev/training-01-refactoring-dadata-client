<?php

declare(strict_types=1);

namespace App\Geocoder\Domain\Exceptions;

class InvalidBicException extends GeocoderException
{
    public function __construct(
        public readonly string $bic,
    ) {
        parent::__construct(sprintf('Неверный формат БИК: %s', $bic));
    }

    public function context(): array
    {
        return ['bic' => $this->bic];
    }
}
