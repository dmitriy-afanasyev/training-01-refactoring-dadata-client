<?php

declare(strict_types=1);

namespace App\Geocoder\Domain\Enums;

/**
 * Перечисление статусов организации.
 */
enum PartyStatus: string
{
    case ACTIVE = 'ACTIVE';
    case LIQUIDATED = 'LIQUIDATED';
    case REORGANIZED = 'REORGANIZED';
    case CLOSING = 'CLOSING';

    /**
     * Проверить, активна ли организация.
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
