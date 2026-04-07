<?php

declare(strict_types=1);

namespace Tests\Unit\Geocoder\Domain\Exceptions;

use App\Geocoder\Domain\Exceptions\InvalidInnException;
use PHPUnit\Framework\TestCase;

class InvalidInnExceptionTest extends TestCase
{
    public function test_message_contains_inn(): void
    {
        $exception = new InvalidInnException('abc');

        $this->assertStringContainsString('abc', $exception->getMessage());
    }

    public function test_context_contains_inn(): void
    {
        $exception = new InvalidInnException('abc');

        $this->assertEquals(['inn' => 'abc'], $exception->context());
    }
}
