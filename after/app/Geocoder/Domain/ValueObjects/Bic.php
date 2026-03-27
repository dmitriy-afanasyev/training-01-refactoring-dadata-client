<?php

declare(strict_types=1);

namespace App\Geocoder\Domain\ValueObjects;

use App\Geocoder\Domain\Exceptions\InvalidBicException;

/**
 * Value Object для БИК банка.
 */
final class Bic
{
    public function __construct(
        private(set) string $value {
            set(string $value) {
                $value = trim($value);

                // БИК должен содержать только цифры
                if (!ctype_digit($value)) {
                    throw new InvalidBicException('БИК должен содержать только цифры');
                }

                // БИК должен содержать 9 цифр
                if (strlen($value) !== 9) {
                    throw new InvalidBicException('БИК должен содержать 9 цифр');
                }

                $this->value = $value;
            }
        }
    ) {}

    /**
     * Создать BIC из строки.
     */
    public static function fromString(string $value): self
    {
        return new self($value);
    }
}
