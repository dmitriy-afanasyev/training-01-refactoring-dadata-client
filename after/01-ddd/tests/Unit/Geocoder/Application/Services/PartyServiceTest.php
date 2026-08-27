<?php

declare(strict_types=1);

namespace Tests\Unit\Geocoder\Application\Services;

use App\Geocoder\Application\Caching\CacheInterface;
use App\Geocoder\Application\DTO\PartyData;
use App\Geocoder\Application\Services\PartyService;
use App\Geocoder\Domain\Entities\Party;
use App\Geocoder\Domain\Enums\PartyStatus;
use App\Geocoder\Domain\Exceptions\ExternalApiException;
use App\Geocoder\Domain\Exceptions\InvalidInnException;
use App\Geocoder\Domain\Exceptions\PartyNotFoundException;
use App\Geocoder\Domain\Repositories\PartyRepositoryInterface;
use App\Geocoder\Domain\ValueObjects\Inn;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

#[CoversClass(PartyService::class)]
class PartyServiceTest extends TestCase
{
    private PartyRepositoryInterface|MockObject $repository;

    private CacheInterface|MockObject $cache;

    private PartyService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(PartyRepositoryInterface::class);
        $this->cache = $this->createMock(CacheInterface::class);
        $this->service = new PartyService($this->repository, $this->cache);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }

    #[TestDox('Возвращает все поля организации по ИНН')]
    public function test_find_by_inn_returns_party_data(): void
    {
        $inn = '7707083893';
        $party = $this->createParty($inn, [
            'name' => 'ПУБЛИЧНОЕ АКЦИОНЕРНОЕ ОБЩЕСТВО "СБЕРБАНК РОССИИ"',
            'shortName' => 'ПАО СБЕРБАНК',
            'kpp' => '773601001',
            'ogrn' => '1027700132195',
            'okpo' => '00032537',
            'address' => 'г Москва, ул Вавилова, д 19',
            'status' => PartyStatus::ACTIVE,
        ]);

        $this->mockCacheRemember($inn);
        $this->repository
            ->expects($this->once())
            ->method('findByInn')
            // Проверяем что сервис передал в репозиторий корректный Inn VO со значением $inn
            ->with($this->callback(fn(Inn $innVO): bool => $innVO->value === $inn))
            ->willReturn($party);

        $result = $this->service->findByInn($inn);

        $expected = new PartyData(
            id: $inn,
            name: 'ПУБЛИЧНОЕ АКЦИОНЕРНОЕ ОБЩЕСТВО "СБЕРБАНК РОССИИ"',
            shortName: 'ПАО СБЕРБАНК',
            inn: $inn,
            kpp: '773601001',
            ogrn: '1027700132195',
            okpo: '00032537',
            address: 'г Москва, ул Вавилова, д 19',
            status: 'ACTIVE',
            isActive: true,
        );

        $this->assertEquals($expected, $result);
    }

    #[TestDox('Использует кэширование при повторных запросах')]
    public function test_find_by_inn_uses_cache(): void
    {
        $inn = '7707083893';
        $party = $this->createParty($inn);

        // Первый вызов — кэш пуст, callback выполняется, репозиторий вызывается.
        // Второй вызов — данные из кэша, репозиторий НЕ вызывается.
        $calls = 0;
        $this->cache
            ->expects($this->exactly(2))
            ->method('remember')
            ->willReturnCallback(function ($key, $ttl, $callback) use ($party, &$calls) {
                $calls++;

                return $calls === 1 ? $callback() : $party->toArray();
            });

        // Репозиторий должен вызваться ровно 1 раз (при первом вызове, второй — из кэша)
        $this->repository
            ->expects($this->once())
            ->method('findByInn')
            ->willReturn($party);

        $result1 = $this->service->findByInn($inn);
        $result2 = $this->service->findByInn($inn);

        $this->assertEquals($result1, $result2);
    }

    #[TestDox('Выбрасывает исключение если организация не найдена')]
    public function test_find_by_inn_throws_party_not_found(): void
    {
        $inn = '7707083893';

        $this->mockCacheRememberThrows($inn, new PartyNotFoundException($inn));

        $this->repository
            ->expects($this->never())
            ->method('findByInn');

        $this->expectException(PartyNotFoundException::class);
        $this->service->findByInn($inn);
    }

    #[TestDox('Выбрасывает исключение при ошибке внешнего API')]
    public function test_find_by_inn_throws_external_api_exception(): void
    {
        $inn = '7707083893';

        $this->mockCacheRememberThrows($inn, new ExternalApiException('API error'));

        $this->repository
            ->expects($this->never())
            ->method('findByInn');

        $this->expectException(ExternalApiException::class);
        $this->service->findByInn($inn);
    }

    #[TestDox('Выбрасывает исключение при невалидном ИНН')]
    #[DataProvider('invalidInnProvider')]
    public function test_find_by_inn_throws_invalid_inn_exception(string $inn): void
    {
        $this->cache
            ->expects($this->once())
            ->method('remember')
            ->willReturnCallback(fn($key, $ttl, $callback) => $callback());

        $this->repository
            ->expects($this->never())
            ->method('findByInn');

        $this->expectException(InvalidInnException::class);
        $this->service->findByInn($inn);
    }

    public static function invalidInnProvider(): array
    {
        return [
            'too short' => ['770708389'],
            'too long' => ['7707083893123'],
            'contains letters' => ['770708389A'],
            'completely invalid' => ['invalid'],
            'empty string' => [''],
        ];
    }

    private function createParty(string $inn, array $attributes = []): Party
    {
        return new Party(
            id: Inn::fromString($inn),
            name: $attributes['name'] ?? 'Test Party',
            shortName: $attributes['shortName'] ?? 'TP',
            inn: Inn::fromString($inn),
            kpp: $attributes['kpp'] ?? null,
            ogrn: $attributes['ogrn'] ?? null,
            okpo: $attributes['okpo'] ?? null,
            address: $attributes['address'] ?? null,
            status: $attributes['status'] ?? null,
        );
    }

    private function mockCacheRemember(string $inn): void
    {
        $this->cache
            ->expects($this->once())
            ->method('remember')
            ->with("geocoder.party.inn.{$inn}", 1440, static::isCallable())
            ->willReturnCallback(fn($key, $ttl, $callback) => $callback());
    }

    private function mockCacheRememberThrows(string $inn, \Throwable $exception): void
    {
        $this->cache
            ->expects($this->once())
            ->method('remember')
            ->with("geocoder.party.inn.{$inn}", 1440, static::isCallable())
            ->willThrowException($exception);
    }
}
