<?php

declare(strict_types=1);

namespace Tests\Unit\Geocoder\Domain\Entities;

use App\Geocoder\Domain\Enums\BankStatus;
use App\Geocoder\Domain\Entities\Bank;
use App\Geocoder\Domain\ValueObjects\Bic;
use App\Geocoder\Domain\ValueObjects\Inn;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Bank::class)]
class BankTest extends TestCase
{
    public function test_create_bank(): void
    {
        $bank = new Bank(
            id: Bic::fromString('044525225'),
            name: 'ПАО "СБЕРБАНК"',
            shortName: 'СБЕРБАНК',
            bic: Bic::fromString('044525225'),
            inn: Inn::fromString('7707083893'),
            correspondentAccount: '30101810400000000225',
            address: 'г. Москва, ул. Вавилова, д. 19',
            status: BankStatus::ACTIVE,
        );

        $this->assertEquals('044525225', $bank->id->value);
        $this->assertEquals('ПАО "СБЕРБАНК"', $bank->name);
        $this->assertEquals('СБЕРБАНК', $bank->shortName);
        $this->assertEquals('044525225', $bank->bic->value);
        $this->assertEquals('7707083893', $bank->inn->value);
        $this->assertEquals('30101810400000000225', $bank->correspondentAccount);
        $this->assertEquals('г. Москва, ул. Вавилова, д. 19', $bank->address);
        $this->assertEquals(BankStatus::ACTIVE, $bank->status);
    }

    public function test_bank_to_array(): void
    {
        $bank = new Bank(
            id: Bic::fromString('044525225'),
            name: 'ПАО "СБЕРБАНК"',
            shortName: 'СБЕРБАНК',
            bic: Bic::fromString('044525225'),
            inn: Inn::fromString('7707083893'),
            correspondentAccount: '30101810400000000225',
            status: BankStatus::ACTIVE,
        );

        $array = $bank->toArray();

        $this->assertEquals([
            'id' => '044525225',
            'name' => 'ПАО "СБЕРБАНК"',
            'short_name' => 'СБЕРБАНК',
            'bic' => '044525225',
            'inn' => '7707083893',
            'correspondent_account' => '30101810400000000225',
            'address' => null,
            'status' => BankStatus::ACTIVE,
            'is_active' => true,
        ], $array);
    }
}
