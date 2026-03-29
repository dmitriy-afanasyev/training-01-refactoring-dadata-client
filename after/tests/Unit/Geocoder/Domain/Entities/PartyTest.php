<?php

declare(strict_types=1);

namespace Tests\Unit\Geocoder\Domain\Entities;

use App\Geocoder\Domain\Entities\Party;
use App\Geocoder\Domain\ValueObjects\Inn;
use PHPUnit\Framework\Attributes\DataProvider;
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

    #[DataProvider('partyStatusProvider')]
    public function test_party_is_active(string $status, bool $expected): void
    {
        $party = new Party(
            name: 'ПАО "СБЕРБАНК"',
            shortName: 'СБЕРБАНК',
            inn: Inn::fromString('7707083893'),
            status: $status,
        );

        $this->assertEquals($expected, $party->isActive());
    }

    /**
     * @return array<int, array{0: string, 1: bool}>
     */
    public static function partyStatusProvider(): array
    {
        return [
            ['ACTIVE', true],
            ['LIQUIDATED', false],
            ['CLOSED', false],
        ];
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
