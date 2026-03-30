<?php

declare(strict_types=1);

namespace Tests\Unit\Geocoder\Application\Services;

use App\Geocoder\Application\DTO\BankData;
use App\Geocoder\Application\Services\BankService;
use App\Geocoder\Domain\Entities\Bank;
use App\Geocoder\Domain\Exceptions\BankNotFoundException;
use App\Geocoder\Domain\Exceptions\InvalidBicException;
use App\Geocoder\Domain\Repositories\BankRepositoryInterface;
use App\Geocoder\Domain\ValueObjects\Bic;
use App\Geocoder\Domain\ValueObjects\Inn;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Тесты для BankService.
 */
class BankServiceTest extends TestCase
{
    private BankRepositoryInterface|MockObject $repository;
    private BankService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(BankRepositoryInterface::class);
        $this->service = new BankService($this->repository);
    }

    public function test_find_by_bic(): void
    {
        $bic = '044525225';

        $bank = new Bank(
            id: Bic::fromString($bic),
            name: 'ПАО "СБЕРБАНК"',
            shortName: 'СБЕРБАНК',
            bic: Bic::fromString($bic),
            inn: Inn::fromString('7707083893'),
        );

        Cache::shouldReceive('remember')
            ->once()
            ->with("geocoder.bank.bic.{$bic}", \Mockery::type('object'), \Mockery::type('callable'))
            ->andReturnUsing(function ($key, $ttl, $callback) {
                return $callback();
            });

        $this->repository
            ->expects($this->once())
            ->method('findByBicOrFail')
            ->with($this->callback(fn(Bic $bicVO): bool => $bicVO->value === $bic))
            ->willReturn($bank);

        $result = $this->service->findByBic($bic);

        $this->assertInstanceOf(BankData::class, $result);
        $this->assertEquals('ПАО "СБЕРБАНК"', $result->name);
        $this->assertEquals('044525225', $result->bic);
        $this->assertEquals('7707083893', $result->inn);
    }

    public function test_find_by_bic_not_found(): void
    {
        $bic = '044525225';

        Cache::shouldReceive('remember')
            ->once()
            ->with("geocoder.bank.bic.{$bic}", \Mockery::type('object'), \Mockery::type('callable'))
            ->andThrow(new BankNotFoundException('Банк не найден'));

        $this->expectException(BankNotFoundException::class);

        $this->service->findByBic($bic);
    }

    public function test_validate_bic_valid(): void
    {
        $this->assertTrue($this->service->validateBic('044525225'));
    }

    public function test_validate_bic_invalid(): void
    {
        $this->assertFalse($this->service->validateBic('04452522'));
        $this->assertFalse($this->service->validateBic('04452522A'));
        $this->assertFalse($this->service->validateBic('invalid'));
    }
}
