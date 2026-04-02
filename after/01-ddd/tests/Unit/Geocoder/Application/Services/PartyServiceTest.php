<?php

declare(strict_types=1);

namespace Tests\Unit\Geocoder\Application\Services;

use App\Geocoder\Application\DTO\PartyData;
use App\Geocoder\Application\Services\PartyService;
use App\Geocoder\Domain\Enums\PartyStatus;
use App\Geocoder\Domain\Entities\Party;
use App\Geocoder\Domain\Exceptions\InvalidInnException;
use App\Geocoder\Domain\Exceptions\PartyNotFoundException;
use App\Geocoder\Domain\Repositories\PartyRepositoryInterface;
use App\Geocoder\Domain\ValueObjects\Inn;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Тесты для PartyService.
 */
class PartyServiceTest extends TestCase
{
    private PartyRepositoryInterface|MockObject $repository;
    private PartyService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(PartyRepositoryInterface::class);
        $this->service = new PartyService($this->repository);
    }

    public function test_find_by_inn(): void
    {
        $inn = '7707083893';

        $party = new Party(
            id: Inn::fromString($inn),
            name: 'ПУБЛИЧНОЕ АКЦИОНЕРНОЕ ОБЩЕСТВО "СБЕРБАНК РОССИИ"',
            shortName: 'ПАО СБЕРБАНК',
            inn: Inn::fromString($inn),
            kpp: '773601001',
            ogrn: '1027700132195',
            okpo: '00032537',
            address: 'г Москва, ул Вавилова, д 19',
            status: PartyStatus::ACTIVE,
        );

        Cache::shouldReceive('remember')
            ->once()
            ->with("geocoder.party.inn.{$inn}", \Mockery::type('object'), \Mockery::type('callable'))
            ->andReturnUsing(function ($key, $ttl, $callback) {
                return $callback();
            });

        $this->repository
            ->expects($this->once())
            ->method('findByInn')
            ->with($this->callback(fn(Inn $innVO): bool => $innVO->value === $inn))
            ->willReturn($party);

        $result = $this->service->findByInn($inn);

        $this->assertInstanceOf(PartyData::class, $result);

        $this->assertEquals($inn, $result->id);
        $this->assertEquals('ПУБЛИЧНОЕ АКЦИОНЕРНОЕ ОБЩЕСТВО "СБЕРБАНК РОССИИ"', $result->name);
        $this->assertEquals('ПАО СБЕРБАНК', $result->shortName);
        $this->assertEquals($inn, $result->inn);
        $this->assertEquals('773601001', $result->kpp);
        $this->assertEquals('1027700132195', $result->ogrn);
        $this->assertEquals('00032537', $result->okpo);
        $this->assertEquals('г Москва, ул Вавилова, д 19', $result->address);
        $this->assertTrue($result->status->isActive());
    }

    public function test_find_entity_by_inn(): void
    {
        $inn = '7707083893';

        $party = new Party(
            id: Inn::fromString($inn),
            name: 'ПАО "СБЕРБАНК"',
            shortName: 'СБЕРБАНК',
            inn: Inn::fromString($inn),
        );

        $this->repository
            ->expects($this->once())
            ->method('findByInn')
            ->willReturn($party);

        $result = $this->service->findEntityByInn($inn);

        $this->assertInstanceOf(Party::class, $result);
        $this->assertEquals('ПАО "СБЕРБАНК"', $result->name);
    }

    public function test_find_by_inn_not_found(): void
    {
        $inn = '7707083893';

        Cache::shouldReceive('remember')
            ->once()
            ->with("geocoder.party.inn.{$inn}", \Mockery::type('object'), \Mockery::type('callable'))
            ->andThrow(new PartyNotFoundException('Организация не найдена'));

        $this->expectException(PartyNotFoundException::class);

        $this->service->findByInn($inn);
    }

    public function test_validate_inn_valid(): void
    {
        $this->assertTrue($this->service->validateInn('7707083893'));
        $this->assertTrue($this->service->validateInn('770708389312'));
    }

    public function test_validate_inn_invalid(): void
    {
        $this->assertFalse($this->service->validateInn('770708389'));
        $this->assertFalse($this->service->validateInn('770708389A'));
        $this->assertFalse($this->service->validateInn('invalid'));
    }
}
