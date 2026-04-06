<?php

declare(strict_types=1);

namespace Tests\Unit\Geocoder\Domain\Enums;

use App\Geocoder\Domain\Enums\BankStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BankStatus::class)]
class BankStatusTest extends TestCase
{
    public function test_is_active_returns_true_for_active(): void
    {
        $this->assertTrue(BankStatus::ACTIVE->isActive());
    }

    public function test_is_active_returns_false_for_liquidated(): void
    {
        $this->assertFalse(BankStatus::LIQUIDATED->isActive());
    }

    public function test_is_active_returns_false_for_reorganized(): void
    {
        $this->assertFalse(BankStatus::REORGANIZED->isActive());
    }

    public function test_is_active_returns_false_for_closing(): void
    {
        $this->assertFalse(BankStatus::CLOSING->isActive());
    }

    public function test_from_string_returns_null_for_null_input(): void
    {
        $this->assertNull(BankStatus::fromString(null));
    }

    public function test_from_string_returns_status_for_valid_value(): void
    {
        $this->assertEquals(BankStatus::ACTIVE, BankStatus::fromString('ACTIVE'));
        $this->assertEquals(BankStatus::LIQUIDATED, BankStatus::fromString('LIQUIDATED'));
        $this->assertEquals(BankStatus::REORGANIZED, BankStatus::fromString('REORGANIZED'));
        $this->assertEquals(BankStatus::CLOSING, BankStatus::fromString('CLOSING'));
    }

    public function test_from_string_returns_active_for_unknown_value(): void
    {
        $this->assertEquals(BankStatus::ACTIVE, BankStatus::fromString('UNKNOWN'));
    }
}
