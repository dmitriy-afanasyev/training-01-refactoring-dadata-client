<?php

declare(strict_types=1);

namespace Tests\Unit\Geocoder\Domain\Exceptions;

use App\Geocoder\Domain\Exceptions\BankNotFoundException;
use PHPUnit\Framework\TestCase;

class BankNotFoundExceptionTest extends TestCase
{
    public function test_message_contains_bic(): void
    {
        $exception = new BankNotFoundException('044525225');

        $this->assertStringContainsString('044525225', $exception->getMessage());
    }

    public function test_context_contains_bic(): void
    {
        $exception = new BankNotFoundException('044525225');

        $this->assertEquals(['bic' => '044525225'], $exception->context());
    }
}
