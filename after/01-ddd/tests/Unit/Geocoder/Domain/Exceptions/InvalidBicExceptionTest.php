<?php

declare(strict_types=1);

namespace Tests\Unit\Geocoder\Domain\Exceptions;

use App\Geocoder\Domain\Exceptions\InvalidBicException;
use PHPUnit\Framework\TestCase;

class InvalidBicExceptionTest extends TestCase
{
    public function test_message_contains_bic(): void
    {
        $exception = new InvalidBicException('abc');

        $this->assertStringContainsString('abc', $exception->getMessage());
    }

    public function test_context_contains_bic(): void
    {
        $exception = new InvalidBicException('abc');

        $this->assertEquals(['bic' => 'abc'], $exception->context());
    }
}
