<?php

declare(strict_types=1);

namespace App\Geocoder\Domain\Exceptions;

/**
 * Исключение выбрасывается, когда банк не найден.
 */
class BankNotFoundException extends GeocoderException
{
    public function __construct(
        public readonly string $bic,
    ) {
        parent::__construct(sprintf('Банк с БИК %s не найден', $bic));
    }

    public function context(): array
    {
        return ['bic' => $this->bic];
    }
}
