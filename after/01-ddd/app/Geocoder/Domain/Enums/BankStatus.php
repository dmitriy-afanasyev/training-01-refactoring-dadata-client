<?php

declare(strict_types=1);

namespace App\Geocoder\Domain\Enums;

/**
 * Перечисление статусов банка.
 */
enum BankStatus: string
{
    case ACTIVE = 'ACTIVE';
    case LIQUIDATED = 'LIQUIDATED';
    case REORGANIZED = 'REORGANIZED';
    case CLOSING = 'CLOSING';

    /**
     * Проверить, активен ли банк.
     */
    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    /**
     * Создать из строки.
     */
    public static function fromString(?string $status): ?self
    {
        if ($status === null) {
            return null;
        }

        return self::tryFrom($status) ?? self::ACTIVE; // По умолчанию ACTIVE
    }
}
