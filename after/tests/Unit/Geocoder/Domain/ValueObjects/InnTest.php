<?php

declare(strict_types=1);

namespace Tests\Unit\Geocoder\Domain\ValueObjects;

use App\Geocoder\Domain\Exceptions\GeocoderException;
use App\Geocoder\Domain\Exceptions\InvalidInnException;
use App\Geocoder\Domain\ValueObjects\Inn;
use PHPUnit\Framework\TestCase;

/**
 * Тесты для Value Object Inn.
 */
class InnTest extends TestCase
{
    public function test_valid_legal_entity_inn(): void
    {
        $inn = Inn::fromString('7707083893');

        $this->assertEquals('7707083893', $inn->value);
        $this->assertTrue($inn->isLegalEntity());
        $this->assertFalse($inn->isIndividualEntrepreneur());
    }

    public function test_valid_individual_entrepreneur_inn(): void
    {
        $inn = Inn::fromString('770708389312');

        $this->assertEquals('770708389312', $inn->value);
        $this->assertFalse($inn->isLegalEntity());
        $this->assertTrue($inn->isIndividualEntrepreneur());
    }

    public function test_invalid_inn_with_letters(): void
    {
        $this->expectException(InvalidInnException::class);

        Inn::fromString('770708389A');
    }

    public function test_invalid_inn_wrong_length(): void
    {
        $this->expectException(InvalidInnException::class);

        Inn::fromString('770708389');
    }

    public function test_invalid_inn_too_long(): void
    {
        $this->expectException(InvalidInnException::class);

        Inn::fromString('7707083893123');
    }

    public function test_inn_with_whitespace_trimmed(): void
    {
        $inn = Inn::fromString('  7707083893  ');

        $this->assertEquals('7707083893', $inn->value);
    }

    public function test_invalid_inn_exception_extends_geocoder_exception(): void
    {
        $this->expectException(GeocoderException::class);

        Inn::fromString('invalid');
    }
}
