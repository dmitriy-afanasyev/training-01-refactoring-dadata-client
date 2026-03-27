<?php

declare(strict_types=1);

namespace Tests\Unit\Geocoder\Domain\Entities;

use App\Geocoder\Domain\Entities\Party;
use App\Geocoder\Domain\ValueObjects\Inn;
use PHPUnit\Framework\TestCase;

/**
 * Тесты для Entity Party.
 */
class PartyTest extends TestCase
{
    public function test_create_party(): void
    {
        $party = new Party(
            name: 'ПАО "СБЕРБАНК"',
            shortName: 'СБЕРБАНК',
            inn: Inn::fromString('7707083893'),
            kpp: '773601001',
            ogrn: '1027700132195',
            okpo: '00032537',
            address: 'г. Москва, ул. Вавилова, д. 19',
            status: 'ACTIVE',
        );

        $this->assertEquals('ПАО "СБЕРБАНК"', $party->name);
        $this->assertEquals('СБЕРБАНК', $party->shortName);
        $this->assertEquals('7707083893', $party->inn->value);
        $this->assertEquals('773601001', $party->kpp);
        $this->assertEquals('1027700132195', $party->ogrn);
        $this->assertEquals('00032537', $party->okpo);
        $this->assertEquals('г. Москва, ул. Вавилова, д. 19', $party->address);
        $this->assertEquals('ACTIVE', $party->status);
    }

    public function test_party_is_active(): void
    {
        $party = new Party(
            name: 'ПАО "СБЕРБАНК"',
            shortName: 'СБЕРБАНК',
            inn: Inn::fromString('7707083893'),
            status: 'ACTIVE',
        );

        $this->assertTrue($party->isActive());
    }

    public function test_party_is_not_active(): void
    {
        $party = new Party(
            name: 'ООО "РОМашКА"',
            shortName: 'РОМАШКА',
            inn: Inn::fromString('7707083893'),
            status: 'LIQUIDATED',
        );

        $this->assertFalse($party->isActive());
    }

    public function test_party_to_array(): void
    {
        $party = new Party(
            name: 'ПАО "СБЕРБАНК"',
            shortName: 'СБЕРБАНК',
            inn: Inn::fromString('7707083893'),
            kpp: '773601001',
            status: 'ACTIVE',
        );

        $array = $party->toArray();

        $this->assertEquals([
            'name' => 'ПАО "СБЕРБАНК"',
            'short_name' => 'СБЕРБАНК',
            'inn' => '7707083893',
            'kpp' => '773601001',
            'ogrn' => null,
            'okpo' => null,
            'address' => null,
            'status' => 'ACTIVE',
        ], $array);
    }
}
