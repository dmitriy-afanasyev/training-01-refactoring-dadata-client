<?php

declare(strict_types=1);

namespace Tests\Unit\Geocoder\Domain\ValueObjects;

use App\Geocoder\Domain\Exceptions\GeocoderException;
use App\Geocoder\Domain\Exceptions\InvalidBicException;
use App\Geocoder\Domain\ValueObjects\Bic;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Bic::class)]
class BicTest extends TestCase
{
    public function test_valid_bic(): void
    {
        $bic = Bic::fromString('044525225');

        $this->assertEquals('044525225', $bic->value);
    }

    public function test_invalid_bic_with_letters(): void
    {
        $this->expectException(InvalidBicException::class);

        Bic::fromString('04452522A');
    }

    public function test_invalid_bic_wrong_length(): void
    {
        $this->expectException(InvalidBicException::class);

        Bic::fromString('04452522');
    }

    public function test_invalid_bic_too_long(): void
    {
        $this->expectException(InvalidBicException::class);

        Bic::fromString('0445252255');
    }

    public function test_bic_with_whitespace_trimmed(): void
    {
        $bic = Bic::fromString('  044525225  ');

        $this->assertEquals('044525225', $bic->value);
    }

    public function test_invalid_bic_exception_extends_geocoder_exception(): void
    {
        $this->expectException(GeocoderException::class);

        Bic::fromString('invalid');
    }
}
