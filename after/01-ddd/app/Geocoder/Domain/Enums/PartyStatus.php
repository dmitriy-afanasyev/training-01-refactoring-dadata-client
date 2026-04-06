<?php

declare(strict_types=1);

namespace App\Geocoder\Domain\Enums;

enum PartyStatus: string
{
    case ACTIVE = 'ACTIVE';
    case LIQUIDATED = 'LIQUIDATED';
    case REORGANIZED = 'REORGANIZED';
    case CLOSING = 'CLOSING';

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    public static function fromString(?string $status): ?self
    {
        if ($status === null) {
            return null;
        }

        return self::tryFrom($status) ?? self::ACTIVE;
    }
}
