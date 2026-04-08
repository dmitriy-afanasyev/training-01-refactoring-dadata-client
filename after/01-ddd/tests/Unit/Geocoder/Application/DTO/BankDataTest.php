<?php

declare(strict_types=1);

namespace Tests\Unit\Geocoder\Application\DTO;

use App\Geocoder\Application\DTO\BankData;
use App\Geocoder\Domain\Enums\BankStatus;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(BankData::class)]
class BankDataTest extends TestCase
{
    public function test_create_from_array(): void
    {
        $data = [
            'id' => '044525225',
            'name' => 'ПАО "СБЕРБАНК"',
            'short_name' => 'СБЕРБАНК',
            'bic' => '044525225',
            'inn' => '7707083893',
            'is_active' => true,
            'correspondent_account' => '30101810400000000225',
            'address' => 'г. Москва, ул. Вавилова, д. 19',
            'status' => 'ACTIVE',
        ];

        $bankData = BankData::fromArray($data);

        $this->assertEquals('044525225', $bankData->id);
        $this->assertEquals('ПАО "СБЕРБАНК"', $bankData->name);
        $this->assertEquals('СБЕРБАНК', $bankData->shortName);
        $this->assertEquals('044525225', $bankData->bic);
        $this->assertEquals('7707083893', $bankData->inn);
        $this->assertEquals('30101810400000000225', $bankData->correspondentAccount);
        $this->assertEquals('г. Москва, ул. Вавилова, д. 19', $bankData->address);
        $this->assertEquals(BankStatus::ACTIVE, $bankData->status);
    }

    public function test_create_with_null_values(): void
    {
        $bankData = BankData::fromArray([
            'id' => '044525225',
            'name' => 'ПАО "СБЕРБАНК"',
            'short_name' => 'СБЕРБАНК',
            'bic' => '044525225',
            'inn' => '7707083893',
            'is_active' => false,
        ]);

        $this->assertEquals('044525225', $bankData->id);
        $this->assertNull($bankData->correspondentAccount);
        $this->assertNull($bankData->address);
        $this->assertNull($bankData->status);
    }
}
