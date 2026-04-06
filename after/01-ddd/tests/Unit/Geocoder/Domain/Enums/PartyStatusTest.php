<?php

declare(strict_types=1);

namespace Tests\Unit\Geocoder\Domain\Enums;

use App\Geocoder\Domain\Enums\PartyStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PartyStatus::class)]
class PartyStatusTest extends TestCase
{
    public function test_is_active_returns_true_for_active(): void
    {
        $this->assertTrue(PartyStatus::ACTIVE->isActive());
    }

    public function test_is_active_returns_false_for_liquidated(): void
    {
        $this->assertFalse(PartyStatus::LIQUIDATED->isActive());
    }

    public function test_is_active_returns_false_for_reorganized(): void
    {
        $this->assertFalse(PartyStatus::REORGANIZED->isActive());
    }

    public function test_is_active_returns_false_for_closing(): void
    {
        $this->assertFalse(PartyStatus::CLOSING->isActive());
    }

    public function test_from_string_returns_null_for_null_input(): void
    {
        $this->assertNull(PartyStatus::fromString(null));
    }

    public function test_from_string_returns_status_for_valid_value(): void
    {
        $this->assertEquals(PartyStatus::ACTIVE, PartyStatus::fromString('ACTIVE'));
        $this->assertEquals(PartyStatus::LIQUIDATED, PartyStatus::fromString('LIQUIDATED'));
        $this->assertEquals(PartyStatus::REORGANIZED, PartyStatus::fromString('REORGANIZED'));
        $this->assertEquals(PartyStatus::CLOSING, PartyStatus::fromString('CLOSING'));
    }

    public function test_from_string_returns_active_for_unknown_value(): void
    {
        $this->assertEquals(PartyStatus::ACTIVE, PartyStatus::fromString('UNKNOWN'));
    }
}
