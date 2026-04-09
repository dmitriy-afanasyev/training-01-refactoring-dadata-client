<?php

declare(strict_types=1);

namespace App\Geocoder\Domain\Exceptions;

class InvalidInnException extends GeocoderException
{
    public function __construct(
        public readonly string $inn,
    ) {
        parent::__construct(sprintf('Неверный формат ИНН: %s', $inn));
    }

    public function context(): array
    {
        return ['inn' => $this->inn];
    }
}
