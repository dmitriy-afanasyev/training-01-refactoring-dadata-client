<?php

declare(strict_types=1);

namespace App\Geocoder\Domain\Exceptions;

/**
 * Исключение выбрасывается, когда банк не найден.
 */
class BankNotFoundException extends GeocoderException
{
}
