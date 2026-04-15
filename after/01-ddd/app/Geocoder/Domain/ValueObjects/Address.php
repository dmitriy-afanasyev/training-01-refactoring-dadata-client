<?php

declare(strict_types=1);

namespace App\Geocoder\Domain\ValueObjects;

use App\Geocoder\Domain\Exceptions\InvalidAddressException;

final class Address
{
    public function __construct(
        private(set) string $value {
            set(string $value) {
                $trimmed = trim($value);

                if ($trimmed === '') {
                    throw new InvalidAddressException($value);
                }

                $this->value = $trimmed;
            }
        }
    ) {}

    public static function fromString(string $value): self
    {
        return new self($value);
    }
}
