<?php

declare(strict_types=1);

namespace Tests\Unit\Geocoder\Presentation\Api\Transformers;

use App\Geocoder\Application\DTO\PartyData;
use App\Geocoder\Domain\Enums\PartyStatus;
use App\Geocoder\Presentation\Api\Transformers\PartyTransformer;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(PartyTransformer::class)]
class PartyTransformerTest extends TestCase
{
    public function test_transform_party_data(): void
    {
        $partyData = new PartyData(
            id: '7707083893',
            name: 'ПАО "СБЕРБАНК"',
            shortName: 'СБЕРБАНК',
            inn: '7707083893',
            kpp: '773601001',
            ogrn: '1027700132195',
            okpo: '00032537',
            address: 'г. Москва, ул. Вавилова, д. 19',
            status: PartyStatus::ACTIVE,
        );

        $transformer = new PartyTransformer();
        $result = $transformer->transform($partyData);

        $this->assertEquals([
            'id' => '7707083893',
            'inn' => '7707083893',
            'name' => 'ПАО "СБЕРБАНК"',
            'short_name' => 'СБЕРБАНК',
            'address' => 'г. Москва, ул. Вавилова, д. 19',
            'status' => 'ACTIVE',
            'is_active' => true,
        ], $result);
    }

    public function test_transform_party_data_with_null_status(): void
    {
        $partyData = new PartyData(
            id: '7707083893',
            name: 'ООО Тест',
            shortName: 'Тест',
            inn: '7707083893',
        );

        $transformer = new PartyTransformer();
        $result = $transformer->transform($partyData);

        $this->assertNull($result['status']);
        $this->assertFalse($result['is_active']);
    }

    public function test_transform_throws_exception_for_invalid_input(): void
    {
        $transformer = new PartyTransformer();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected PartyData');

        $transformer->transform('invalid_data');
    }
}
