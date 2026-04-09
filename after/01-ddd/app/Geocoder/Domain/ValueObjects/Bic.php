<?php

declare(strict_types=1);

namespace App\Geocoder\Domain\ValueObjects;

use App\Geocoder\Domain\Exceptions\InvalidBicException;

final class Bic
{
    private const LENGTH = 9;

    public function __construct(
        private(set) string $value {
            set(string $value) {
                $value = trim($value);

                if (!ctype_digit($value)) {
                    throw new InvalidBicException('БИК должен содержать только цифры');
                }

                if (strlen($value) !== self::LENGTH) {
                    throw new InvalidBicException('БИК должен содержать 9 цифр');
                }

                $this->value = $value;
            }
        }
    ) {}

    public static function fromString(string $value): self
    {
        return new self($value);
    }
}
