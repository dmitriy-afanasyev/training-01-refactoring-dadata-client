<?php

declare(strict_types=1);

namespace Tests\Unit\Geocoder\Infrastructure\Persistence;

use App\Geocoder\Domain\ValueObjects\Address;
use App\Geocoder\Infrastructure\Http\Dadata\DadataApiInterface;
use App\Geocoder\Infrastructure\Persistence\DadataAddressRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(DadataAddressRepository::class)]
class DadataAddressRepositoryTest extends TestCase
{
    private DadataApiInterface|MockObject $api;
    private DadataAddressRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->api = $this->createMock(DadataApiInterface::class);
        $this->repository = new DadataAddressRepository($this->api);
    }

    public function test_search_address_returns_address_objects(): void
    {
        $this->api
            ->expects($this->once())
            ->method('searchAddress')
            ->with('Ленина', null)
            ->willReturn(['г. Москва, ул. Ленина', 'г. Санкт-Петербург, ул. Ленина']);

        $result = $this->repository->searchAddress('Ленина');

        $this->assertCount(2, $result);
        $this->assertInstanceOf(Address::class, $result[0]);
        $this->assertInstanceOf(Address::class, $result[1]);
        $this->assertEquals('г. Москва, ул. Ленина', $result[0]->value);
        $this->assertEquals('г. Санкт-Петербург, ул. Ленина', $result[1]->value);
    }

    public function test_search_address_with_locations(): void
    {
        $locations = ['region' => 'москва'];

        $this->api
            ->expects($this->once())
            ->method('searchAddress')
            ->with('Ленина', $locations)
            ->willReturn(['г. Москва, ул. Ленина']);

        $result = $this->repository->searchAddress('Ленина', $locations);

        $this->assertCount(1, $result);
        $this->assertEquals('г. Москва, ул. Ленина', $result[0]->value);
    }

    public function test_search_address_returns_empty_array(): void
    {
        $this->api
            ->method('searchAddress')
            ->willReturn([]);

        $result = $this->repository->searchAddress('НесуществующийАдрес');

        $this->assertEmpty($result);
    }

    public function test_search_country(): void
    {
        $this->api
            ->expects($this->once())
            ->method('searchCountry')
            ->with('Россия')
            ->willReturn(['Россия', 'Российская Федерация']);

        $result = $this->repository->searchCountry('Россия');

        $this->assertEquals(['Россия', 'Российская Федерация'], $result);
    }
}
