<?php

declare(strict_types=1);

namespace Tests\Unit\Geocoder\Infrastructure\Persistence;

use App\Geocoder\Domain\Enums\PartyStatus;
use App\Geocoder\Domain\Exceptions\PartyNotFoundException;
use App\Geocoder\Domain\ValueObjects\Inn;
use App\Geocoder\Infrastructure\Http\Dadata\DadataApiInterface;
use App\Geocoder\Infrastructure\Persistence\DadataPartyRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(DadataPartyRepository::class)]
class DadataPartyRepositoryTest extends TestCase
{
    private DadataApiInterface|MockObject $api;
    private DadataPartyRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->api = $this->createMock(DadataApiInterface::class);
        $this->repository = new DadataPartyRepository($this->api);
    }

    public function test_find_by_inn_returns_party(): void
    {
        $innValue = '7707083893';

        $this->api
            ->expects($this->once())
            ->method('findPartyByInn')
            ->with($innValue)
            ->willReturn($this->fullPartyData());

        $party = $this->repository->findByInn(Inn::fromString($innValue));

        $this->assertEquals($innValue, $party->id->value);
        $this->assertEquals('ПАО "СБЕРБАНК"', $party->name);
        $this->assertEquals('ПАО СБЕРБАНК', $party->shortName);
        $this->assertEquals($innValue, $party->inn->value);
        $this->assertEquals('773601001', $party->kpp);
        $this->assertEquals('1027700132195', $party->ogrn);
        $this->assertEquals('00032537', $party->okpo);
        $this->assertEquals('г. Москва, ул. Вавилова, д. 19', $party->address);
        $this->assertEquals(PartyStatus::ACTIVE, $party->status);
    }

    public function test_find_by_inn_throws_not_found_when_api_returns_null(): void
    {
        $innValue = '7707083893';

        $this->api
            ->method('findPartyByInn')
            ->with($innValue)
            ->willReturn(null);

        $this->expectException(PartyNotFoundException::class);
        $this->expectExceptionMessage($innValue);

        $this->repository->findByInn(Inn::fromString($innValue));
    }

    public function test_find_by_inn_maps_null_fields(): void
    {
        $innValue = '7707083893';

        $this->api
            ->method('findPartyByInn')
            ->willReturn([
                'name' => ['full_with_opf' => 'ООО Тест'],
                'inn' => $innValue,
            ]);

        $party = $this->repository->findByInn(Inn::fromString($innValue));

        $this->assertNull($party->kpp);
        $this->assertNull($party->ogrn);
        $this->assertNull($party->okpo);
        $this->assertNull($party->address);
        $this->assertNull($party->status);
    }

    public function test_find_by_inn_uses_short_name_when_full_missing(): void
    {
        $innValue = '7707083893';

        $this->api
            ->method('findPartyByInn')
            ->willReturn([
                'name' => ['short_with_opf' => 'ООО Тест'],
                'inn' => $innValue,
            ]);

        $party = $this->repository->findByInn(Inn::fromString($innValue));

        $this->assertEquals('ООО Тест', $party->name);
        $this->assertEquals('ООО Тест', $party->shortName);
    }

    private function fullPartyData(): array
    {
        return [
            'name' => ['full_with_opf' => 'ПАО "СБЕРБАНК"', 'short_with_opf' => 'ПАО СБЕРБАНК'],
            'inn' => '7707083893',
            'kpp' => '773601001',
            'ogrn' => '1027700132195',
            'okpo' => '00032537',
            'address' => ['value' => 'г. Москва, ул. Вавилова, д. 19'],
            'state' => ['status' => 'ACTIVE'],
        ];
    }
}
