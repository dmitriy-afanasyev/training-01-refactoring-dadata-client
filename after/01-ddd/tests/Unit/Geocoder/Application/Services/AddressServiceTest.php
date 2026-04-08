<?php

declare(strict_types=1);

namespace Tests\Unit\Geocoder\Application\Services;

use App\Geocoder\Application\Services\AddressService;
use App\Geocoder\Domain\Exceptions\ExternalApiException;
use App\Geocoder\Domain\Repositories\AddressRepositoryInterface;
use App\Geocoder\Domain\ValueObjects\Address;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(AddressService::class)]
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
            Address::fromString('г Москва, ул Ботаническая'),
            Address::fromString('г Москва, ул Малая Ботаническая'),
            Address::fromString('г Санкт-Петербург, г Петергоф, ул Ботаническая'),
            Address::fromString('г Казань, ул Ботаническая (Константиновка)'),
        ];

        Cache::shouldReceive('remember')
            ->once()
            ->andReturnUsing(function ($key, $ttl, $callback) {
                return $callback();
            });

        $this->repository
            ->expects($this->once())
            ->method('searchAddress')
            ->with('Ботаническая', null)
            ->willReturn($addresses);

        $result = $this->service->searchAddress('Ботаническая');

        $this->assertEquals([
            "г Москва, ул Ботаническая",
            "г Москва, ул Малая Ботаническая",
            "г Санкт-Петербург, г Петергоф, ул Ботаническая",
            "г Казань, ул Ботаническая (Константиновка)"
        ], $result);
    }

    public function test_search_addresses_with_locations(): void
    {
        $query = 'Ботаническая';
        $locations = ['region' => 'москва'];
        $addresses = [
            Address::fromString('г Москва, ул Ботаническая'),
            Address::fromString('г Москва, ул Малая Ботаническая'),
        ];

        Cache::shouldReceive('remember')
            ->once()
            ->andReturnUsing(function ($key, $ttl, $callback) {
                return $callback();
            });

        $this->repository
            ->expects($this->once())
            ->method('searchAddress')
            ->with($query, $locations)
            ->willReturn($addresses);

        $result = $this->service->searchAddress($query, $locations);

        $this->assertEquals([
            "г Москва, ул Ботаническая",
            "г Москва, ул Малая Ботаническая"
        ], $result);
    }

    public function test_search_country(): void
    {
        Cache::shouldReceive('remember')
            ->once()
            ->andReturnUsing(function ($key, $ttl, $callback) {
                return $callback();
            });

        $this->repository
            ->expects($this->once())
            ->method('searchCountry')
            ->with('Россия')
            ->willReturn(['Россия', 'Российская Федерация']);

        $result = $this->service->searchCountry('Россия');

        $this->assertEquals(['Россия', 'Российская Федерация'], $result);
    }

    public function test_search_address_throws_external_api_exception(): void
    {
        Cache::shouldReceive('remember')
            ->once()
            ->andReturnUsing(function ($key, $ttl, $callback) {
                return $callback();
            });

        $exception = new ExternalApiException('External API error', 500);

        $this->repository
            ->expects($this->once())
            ->method('searchAddress')
            ->with('Неизвестный адрес', null)
            ->willThrowException($exception);

        $this->expectException(ExternalApiException::class);
        $this->expectExceptionMessage('External API error');

        $this->service->searchAddress('Неизвестный адрес');
    }

    public function test_search_country_throws_external_api_exception(): void
    {
        Cache::shouldReceive('remember')
            ->once()
            ->andReturnUsing(function ($key, $ttl, $callback) {
                return $callback();
            });

        $exception = new ExternalApiException('External API error', 503);

        $this->repository
            ->expects($this->once())
            ->method('searchCountry')
            ->with('Неизвестная страна')
            ->willThrowException($exception);

        $this->expectException(ExternalApiException::class);
        $this->expectExceptionMessage('External API error');

        $this->service->searchCountry('Неизвестная страна');
    }
}
