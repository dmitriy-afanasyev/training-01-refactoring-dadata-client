<?php

declare(strict_types=1);

namespace Tests\Unit\Geocoder\Application\DTO;

use App\Geocoder\Application\DTO\PartyData;
use App\Geocoder\Domain\Enums\PartyStatus;
use PHPUnit\Framework\TestCase;

/**
 * Тесты для DTO PartyData.
 */
class PartyDataTest extends TestCase
{
    public function test_create_from_array(): void
    {
        $data = [
            'id' => '7707083893',
            'name' => 'ПАО "СБЕРБАНК"',
            'short_name' => 'СБЕРБАНК',
            'inn' => '7707083893',
            'kpp' => '773601001',
            'ogrn' => '1027700132195',
            'okpo' => '00032537',
            'address' => 'г. Москва, ул. Вавилова, д. 19',
            'status' => 'ACTIVE',
        ];

        $partyData = PartyData::fromArray($data);

        $this->assertEquals('7707083893', $partyData->id);
        $this->assertEquals('ПАО "СБЕРБАНК"', $partyData->name);
        $this->assertEquals('СБЕРБАНК', $partyData->shortName);
        $this->assertEquals('7707083893', $partyData->inn);
        $this->assertEquals('773601001', $partyData->kpp);
        $this->assertEquals('1027700132195', $partyData->ogrn);
        $this->assertEquals('00032537', $partyData->okpo);
        $this->assertEquals('г. Москва, ул. Вавилова, д. 19', $partyData->address);
        $this->assertEquals(PartyStatus::ACTIVE, $partyData->status);
    }

    public function test_create_with_null_values(): void
    {
        $partyData = PartyData::fromArray([
            'id' => '7707083893',
            'name' => 'ООО "РОМАШКА"',
            'short_name' => 'РОМАШКА',
            'inn' => '7707083893',
        ]);

        $this->assertEquals('7707083893', $partyData->id);
        $this->assertEquals('ООО "РОМАШКА"', $partyData->name);
        $this->assertNull($partyData->kpp);
        $this->assertNull($partyData->ogrn);
        $this->assertNull($partyData->okpo);
    }
}
