<?php

declare(strict_types=1);

namespace Tests\Unit\Geocoder\Domain\Exceptions;

use App\Geocoder\Domain\Exceptions\InvalidAddressException;
use PHPUnit\Framework\TestCase;

class InvalidAddressExceptionTest extends TestCase
{
    public function test_message_contains_address(): void
    {
        $exception = new InvalidAddressException('  ');

        $this->assertStringContainsString('  ', $exception->getMessage());
    }

    public function test_context_contains_address(): void
    {
        $exception = new InvalidAddressException('  ');

        $this->assertEquals(['address' => '  '], $exception->context());
    }
}
