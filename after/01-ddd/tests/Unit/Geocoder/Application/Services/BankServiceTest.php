<?php

declare(strict_types=1);

namespace Tests\Unit\Geocoder\Application\Services;

use App\Geocoder\Application\Caching\CacheInterface;
use App\Geocoder\Application\DTO\BankData;
use App\Geocoder\Application\Services\BankService;
use App\Geocoder\Domain\Entities\Bank;
use App\Geocoder\Domain\Enums\BankStatus;
use App\Geocoder\Domain\Exceptions\BankNotFoundException;
use App\Geocoder\Domain\Exceptions\ExternalApiException;
use App\Geocoder\Domain\Exceptions\InvalidBicException;
use App\Geocoder\Domain\Repositories\BankRepositoryInterface;
use App\Geocoder\Domain\ValueObjects\Bic;
use App\Geocoder\Domain\ValueObjects\Inn;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

#[CoversClass(BankService::class)]
class BankServiceTest extends TestCase
{
    private BankRepositoryInterface|MockObject $repository;

    private CacheInterface|MockObject $cache;

    private BankService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(BankRepositoryInterface::class);
        $this->cache = $this->createMock(CacheInterface::class);
        $this->service = new BankService($this->repository, $this->cache);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }

    #[TestDox('Возвращает все поля банка по БИК')]
    public function test_find_by_bic_returns_bank_data(): void
    {
        $bic = '044525225';

        $bank = new Bank(
            id: Bic::fromString($bic),
            name: 'ПАО "СБЕРБАНК"',
            shortName: 'СБЕРБАНК',
            bic: Bic::fromString($bic),
            inn: Inn::fromString('7707083893'),
            status: BankStatus::ACTIVE,
        );

        $this->cache
            ->expects($this->once())
            ->method('remember')
            ->with("geocoder.bank.bic.{$bic}", 1440, static::isCallable())
            ->willReturnCallback(fn ($key, $ttl, $callback) => $callback());

        $this->repository
            ->expects($this->once())
            ->method('findByBicOrFail')
            ->with($this->callback(fn (Bic $bicVO): bool => $bicVO->value === $bic))
            ->willReturn($bank);

        $result = $this->service->findByBic($bic);

        $expected = new BankData(
            id: $bic,
            name: 'ПАО "СБЕРБАНК"',
            shortName: 'СБЕРБАНК',
            bic: $bic,
            inn: '7707083893',
            isActive: true,
            status: BankStatus::ACTIVE,
        );

        $this->assertEquals($expected, $result);
    }

    #[TestDox('Использует кэширование при повторных запросах')]
    public function test_find_by_bic_uses_cache(): void
    {
        $bic = '044525225';
        $bank = $this->createBank($bic);

        // Первый вызов — кэш пуст, callback выполняется, репозиторий вызывается.
        // Второй вызов — данные из кэша, репозиторий НЕ вызывается.
        $calls = 0;
        $this->cache
            ->expects($this->exactly(2))
            ->method('remember')
            ->willReturnCallback(function ($key, $ttl, $callback) use ($bank, &$calls) {
                $calls++;

                return $calls === 1 ? $callback() : $bank->toArray();
            });

        // Репозиторий должен вызваться ровно 1 раз (при первом вызове, второй — из кэша)
        $this->repository
            ->expects($this->once())
            ->method('findByBicOrFail')
            ->willReturn($bank);

        $result1 = $this->service->findByBic($bic);
        $result2 = $this->service->findByBic($bic);

        $this->assertEquals($result1, $result2);
    }

    #[TestDox('Выбрасывает исключение если банк не найден')]
    #[AllowMockObjectsWithoutExpectations]
    public function test_find_by_bic_throws_bank_not_found(): void
    {
        $bic = '044525225';

        $this->mockCacheRememberThrows($bic, new BankNotFoundException($bic));

        $this->expectException(BankNotFoundException::class);
        $this->service->findByBic($bic);
    }

    #[TestDox('Выбрасывает исключение при ошибке внешнего API')]
    #[AllowMockObjectsWithoutExpectations]
    public function test_find_by_bic_throws_external_api_exception(): void
    {
        $bic = '044525225';

        $this->mockCacheRememberThrows($bic, new ExternalApiException('API error'));

        $this->expectException(ExternalApiException::class);
        $this->service->findByBic($bic);
    }

    #[TestDox('Выбрасывает исключение при невалидном БИК')]
    #[AllowMockObjectsWithoutExpectations]
    #[DataProvider('invalidBicProvider')]
    public function test_find_by_bic_throws_invalid_bic_exception(string $bic): void
    {
        $this->cache
            ->expects($this->once())
            ->method('remember')
            ->willReturnCallback(fn ($key, $ttl, $callback) => $callback());

        $this->expectException(InvalidBicException::class);
        $this->service->findByBic($bic);
    }

    public static function invalidBicProvider(): array
    {
        return [
            'too short' => ['04452522'],
            'too long' => ['0445252255'],
            'contains letters' => ['04452522A'],
            'completely invalid' => ['invalid'],
            'empty string' => [''],
        ];
    }

    private function createBank(string $bic, array $attributes = []): Bank
    {
        return new Bank(
            id: Bic::fromString($bic),
            name: $attributes['name'] ?? 'Test Bank',
            shortName: $attributes['shortName'] ?? 'TB',
            bic: Bic::fromString($bic),
            inn: Inn::fromString($attributes['inn'] ?? '7707083893'),
            status: $attributes['status'] ?? null,
        );
    }

    private function mockCacheRememberThrows(string $bic, \Throwable $exception): void
    {
        $this->cache
            ->expects($this->once())
            ->method('remember')
            ->with("geocoder.bank.bic.{$bic}", 1440, static::isCallable())
            ->willThrowException($exception);
    }
}
