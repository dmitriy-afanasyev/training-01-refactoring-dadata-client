<?php

declare(strict_types=1);

namespace App\Geocoder\Domain\ValueObjects;

use App\Geocoder\Domain\Exceptions\InvalidInnException;

final class Inn
{
    private const LENGTH_LEGAL_ENTITY = 10;
    private const LENGTH_INDIVIDUAL_ENTREPRENEUR = 12;

    private const VALID_LENGTHS = [
        self::LENGTH_LEGAL_ENTITY,
        self::LENGTH_INDIVIDUAL_ENTREPRENEUR,
    ];

    public function __construct(
        private(set) string $value {
            set(string $value) {
                $value = trim($value);

                if (!ctype_digit($value)) {
                    throw new InvalidInnException('ИНН должен содержать только цифры');
                }

                if (!in_array(strlen($value), self::VALID_LENGTHS, true)) {
                    throw new InvalidInnException('ИНН должен содержать '
                        . self::LENGTH_LEGAL_ENTITY
                        . ' или '
                        . self::LENGTH_INDIVIDUAL_ENTREPRENEUR
                        . ' цифр');
                }

                $this->value = $value;
            }
        }
    ) {
        //
    }

    public static function fromString(string $inn): self
    {
        return new self($inn);
    }

    public function isLegalEntity(): bool
    {
        return strlen($this->value) === self::LENGTH_LEGAL_ENTITY;
    }

    public function isIndividualEntrepreneur(): bool
    {
        return strlen($this->value) === self::LENGTH_INDIVIDUAL_ENTREPRENEUR;
    }
}
