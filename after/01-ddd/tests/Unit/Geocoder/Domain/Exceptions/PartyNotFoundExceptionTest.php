<?php

declare(strict_types=1);

namespace Tests\Unit\Geocoder\Domain\Exceptions;

use App\Geocoder\Domain\Exceptions\PartyNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PartyNotFoundException::class)]
class PartyNotFoundExceptionTest extends TestCase
{
    public function test_message_contains_inn(): void
    {
        $exception = new PartyNotFoundException('7707083893');

        $this->assertStringContainsString('7707083893', $exception->getMessage());
    }

    public function test_context_contains_inn(): void
    {
        $exception = new PartyNotFoundException('7707083893');

        $this->assertEquals(['inn' => '7707083893'], $exception->context());
    }
}
