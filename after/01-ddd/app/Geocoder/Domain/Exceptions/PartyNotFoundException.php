<?php

declare(strict_types=1);

namespace App\Geocoder\Domain\Exceptions;

class PartyNotFoundException extends GeocoderException
{
    public function __construct(
        public readonly string $inn,
    ) {
        parent::__construct(sprintf('Организация с ИНН %s не найдена', $inn));
    }

    public function context(): array
    {
        return ['inn' => $this->inn];
    }
}
