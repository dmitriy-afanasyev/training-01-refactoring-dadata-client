<?php

declare(strict_types=1);

namespace Tests\Unit\Geocoder\Domain\ValueObjects;

use App\Geocoder\Domain\Exceptions\InvalidAddressException;
use App\Geocoder\Domain\ValueObjects\Address;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Address::class)]
class AddressTest extends TestCase
{
    public function test_create_from_string(): void
    {
        $address = Address::fromString('г. Москва, ул. Ленина, д. 1');

        $this->assertEquals('г. Москва, ул. Ленина, д. 1', $address->value);
    }

    public function test_trims_whitespace(): void
    {
        $address = Address::fromString('  г. Москва, ул. Ленина, д. 1  ');

        $this->assertEquals('г. Москва, ул. Ленина, д. 1', $address->value);
    }

    public function test_throws_for_empty_string(): void
    {
        $this->expectException(InvalidAddressException::class);

        Address::fromString('');
    }

    public function test_throws_for_whitespace_only(): void
    {
        $this->expectException(InvalidAddressException::class);

        Address::fromString('   ');
    }
}
