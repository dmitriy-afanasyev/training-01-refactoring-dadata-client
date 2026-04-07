<?php

declare(strict_types=1);

namespace Tests\Unit\Geocoder\Domain\Exceptions;

use App\Geocoder\Domain\Exceptions\GeocoderException;
use PHPUnit\Framework\TestCase;

class GeocoderExceptionTest extends TestCase
{
    public function test_message(): void
    {
        $exception = new GeocoderException('Some error');

        $this->assertSame('Some error', $exception->getMessage());
    }

    public function test_context_returns_empty_array(): void
    {
        $exception = new GeocoderException('Error');

        $this->assertEquals([], $exception->context());
    }
}
