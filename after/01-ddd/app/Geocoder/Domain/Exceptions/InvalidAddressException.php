<?php

declare(strict_types=1);

namespace App\Geocoder\Domain\Exceptions;

class InvalidAddressException extends GeocoderException
{
    public function __construct(
        public readonly string $address,
    ) {
        parent::__construct(sprintf('Неверный формат адреса: %s', $address));
    }

    public function context(): array
    {
        return ['address' => $this->address];
    }
}
