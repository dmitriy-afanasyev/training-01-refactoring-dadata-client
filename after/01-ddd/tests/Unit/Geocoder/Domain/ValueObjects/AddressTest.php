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

    public function test_empty_string_after_trim_throws_exception(): void
    {
        $this->expectException(InvalidAddressException::class);

        Address::fromString('  ');
    }

    public function test_special_characters_allowed(): void
    {
        $address = Address::fromString('г. Москва, ул. Ленина, д. 1, кв. 5!');

        $this->assertEquals('г. Москва, ул. Ленина, д. 1, кв. 5!', $address->value);
    }

    public function test_unicode_characters_allowed(): void
    {
        $address = Address::fromString('г. Казань, ул. Баумана, д. 1');

        $this->assertEquals('г. Казань, ул. Баумана, д. 1', $address->value);
    }

    public function test_max_length_allowed(): void
    {
        $longAddress = str_repeat('г. Москва, ул. ', 10) . 'д. 1';

        $address = Address::fromString($longAddress);

        $this->assertEquals($longAddress, $address->value);
    }
}
