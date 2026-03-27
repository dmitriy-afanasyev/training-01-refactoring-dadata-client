<?php

declare(strict_types=1);

namespace Tests\Unit\Geocoder\Application\Services;

use App\Geocoder\Application\Services\AddressService;
use App\Geocoder\Domain\Entities\Address;
use App\Geocoder\Domain\Repositories\AddressRepositoryInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Тесты для AddressService.
 */
class AddressServiceTest extends TestCase
{
    private AddressRepositoryInterface|MockObject $repository;
    private AddressService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(AddressRepositoryInterface::class);
        $this->service = new AddressService($this->repository);
    }

    public function test_search_addresses(): void
    {
        $addresses = [
            new Address(value: 'г. Москва'),
            new Address(value: 'Московская область'),
        ];

        $this->repository
            ->expects($this->once())
            ->method('search')
            ->with('Москва', null)
            ->willReturn($addresses);

        $result = $this->service->search('Москва');

        $this->assertEquals(['г. Москва', 'Московская область'], $result);
    }

    public function test_search_addresses_with_locations(): void
    {
        $locations = ['city_code' => '77'];

        $addresses = [
            new Address(value: 'г. Москва'),
        ];

        $this->repository
            ->expects($this->once())
            ->method('search')
            ->with('Москва', $locations)
            ->willReturn($addresses);

        $result = $this->service->search('Москва', $locations);

        $this->assertEquals(['г. Москва'], $result);
    }

    public function test_search_country(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('searchCountry')
            ->with('Россия')
            ->willReturn(['Россия', 'Российская Федерация']);

        $result = $this->service->searchCountry('Россия');

        $this->assertEquals(['Россия', 'Российская Федерация'], $result);
    }
}
