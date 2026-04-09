<?php

declare(strict_types=1);

namespace App\Geocoder\Domain\ValueObjects;

final class Address
{
    public function __construct(
        private(set) string $value {
            set(string $value) {
                $value = trim($value);

                if ($value === '') {
                    throw new \InvalidArgumentException('Адрес не может быть пустым');
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
