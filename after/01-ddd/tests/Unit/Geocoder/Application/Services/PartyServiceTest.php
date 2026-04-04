<?php

declare(strict_types=1);

namespace Tests\Unit\Geocoder\Application\Services;

use App\Geocoder\Application\DTO\PartyData;
use App\Geocoder\Application\Services\PartyService;
use App\Geocoder\Domain\Enums\PartyStatus;
use App\Geocoder\Domain\Entities\Party;
use App\Geocoder\Domain\Exceptions\ExternalApiException;
use App\Geocoder\Domain\Exceptions\InvalidInnException;
use App\Geocoder\Domain\Exceptions\PartyNotFoundException;
use App\Geocoder\Domain\Repositories\PartyRepositoryInterface;
use App\Geocoder\Domain\ValueObjects\Inn;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\DataProvider;
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

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }

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

        $this->assertPartyData($inn, $result);
    }

    public function test_find_by_inn_uses_cache(): void
    {
        $inn = '7707083893';
        $party = $this->createParty($inn);

        // Первый вызов — кэш пуст, callback выполняется, репозиторий вызывается
        Cache::shouldReceive('remember')
            ->once()
            ->andReturnUsing(fn($k, $t, $cb) => $cb());

        // Репозиторий должен вызваться ровно 1 раз (при первом вызове, второй — из кэша)
        $this->repository
            ->expects($this->once())
            ->method('findByInn')
            ->willReturn($party);

        $result1 = $this->service->findByInn($inn);

        // Второй вызов — данные из кэша, репозиторий НЕ вызывается
        Cache::shouldReceive('remember')
            ->once()
            ->andReturn($party->toArray());

        $result2 = $this->service->findByInn($inn);

        $this->assertEquals($result1, $result2);
    }

    public function test_find_by_inn_throws_party_not_found(): void
    {
        $inn = '7707083893';

        $this->mockCacheRememberThrows($inn, new PartyNotFoundException($inn));

        $this->expectException(PartyNotFoundException::class);
        $this->service->findByInn($inn);
    }

    public function test_find_by_inn_throws_external_api_exception(): void
    {
        $inn = '7707083893';

        $this->mockCacheRememberThrows($inn, new ExternalApiException('API error'));

        $this->expectException(ExternalApiException::class);
        $this->service->findByInn($inn);
    }

    public function test_find_by_inn_throws_invalid_inn_exception(): void
    {
        Cache::shouldReceive('remember')
            ->once()
            ->andReturnUsing(fn($key, $ttl, $callback) => $callback());

        $this->expectException(InvalidInnException::class);
        $this->service->findByInn('invalid');
    }

    #[DataProvider('validInnProvider')]
    public function test_validate_inn_valid(string $inn): void
    {
        $this->assertTrue($this->service->validateInn($inn));
    }

    #[DataProvider('invalidInnProvider')]
    public function test_validate_inn_invalid(string $inn): void
    {
        $this->assertFalse($this->service->validateInn($inn));
    }

    public static function validInnProvider(): array
    {
        return [
            'legal entity (10 digits)' => ['7707083893'],
            'individual entrepreneur (12 digits)' => ['770708389312'],
        ];
    }

    public static function invalidInnProvider(): array
    {
        return [
            'too short' => ['770708389'],
            'contains letters' => ['770708389A'],
            'completely invalid' => ['invalid'],
            'empty string' => [''],
        ];
    }

    /**
     * Создать тестовую сущность Party.
     */
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

    /**
     * Проверить данные PartyData.
     */
    private function assertPartyData(string $inn, PartyData $result): void
    {
        $this->assertEquals($inn, $result->id);
        $this->assertEquals($inn, $result->inn);
    }

    /**
     * Замокать Cache::remember с выполнением callback.
     */
    private function mockCacheRemember(string $inn): void
    {
        Cache::shouldReceive('remember')
            ->once()
            ->with("geocoder.party.inn.{$inn}", static::isInstanceOf(\DateTimeInterface::class), static::isCallable())
            ->andReturnUsing(fn($key, $ttl, $callback) => $callback());
    }

    /**
     * Замокать Cache::remember с выбросом исключения.
     */
    private function mockCacheRememberThrows(string $inn, \Throwable $exception): void
    {
        Cache::shouldReceive('remember')
            ->once()
            ->with("geocoder.party.inn.{$inn}", static::isInstanceOf(\DateTimeInterface::class), static::isCallable())
            ->andThrow($exception);
    }
}
