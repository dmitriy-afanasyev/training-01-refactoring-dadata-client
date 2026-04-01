<?php

declare(strict_types=1);

namespace App\Geocoder\Domain\ValueObjects;

use App\Geocoder\Domain\Exceptions\InvalidInnException;

/**
 * Value Object для ИНН юридического лица.
 */
final class Inn
{
    /**
     * Количество знаков ИНН юридического лица.
     */
    private const LENGTH_LEGAL_ENTITY = 10;

    /**
     * Количество знаков ИНН индивидуального предпринимателя.
     */
    private const LENGTH_INDIVIDUAL_ENTREPRENEUR = 12;

    /**
     * Допустимые длины ИНН.
     */
    private const VALID_LENGTHS = [
        self::LENGTH_LEGAL_ENTITY,
        self::LENGTH_INDIVIDUAL_ENTREPRENEUR,
    ];

    /**
     * Конструктор с хуками для валидации и инкапсуляции.
     *
     * @param string $value Значение ИНН
     */
    public function __construct(
        private(set) string $value {
            set(string $value) {
                $value = trim($value);

                if (!ctype_digit($value)) {
                    throw new InvalidInnException('ИНН должен содержать только цифры');
                }

                if (!in_array(strlen($value), self::VALID_LENGTHS, true)) {
                    throw new InvalidInnException('ИНН должен содержать 10 или 12 цифр');
                }

                $this->value = $value;
            }
        }
    ) {
        //
    }

    /**
     * Создать INN из строки.
     */
    public static function fromString(string $value): self
    {
        return new self($value);
    }

    /**
     * Проверить, является ли ИНН юрлицом (10 знаков).
     */
    public function isLegalEntity(): bool
    {
        return strlen($this->value) === self::LENGTH_LEGAL_ENTITY;
    }

    /**
     * Проверить, является ли ИНН ИП (12 знаков).
     */
    public function isIndividualEntrepreneur(): bool
    {
        return strlen($this->value) === self::LENGTH_INDIVIDUAL_ENTREPRENEUR;
    }
}
