<?php

declare(strict_types=1);

namespace Tests\Unit\Geocoder\Infrastructure\Persistence;

use App\Geocoder\Domain\Enums\BankStatus;
use App\Geocoder\Domain\Exceptions\BankNotFoundException;
use App\Geocoder\Domain\Exceptions\ExternalApiException;
use App\Geocoder\Domain\ValueObjects\Bic;
use App\Geocoder\Infrastructure\Http\Dadata\DadataApiInterface;
use App\Geocoder\Infrastructure\Persistence\DadataBankRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(DadataBankRepository::class)]
class DadataBankRepositoryTest extends TestCase
{
    private DadataApiInterface|MockObject $api;
    private DadataBankRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->api = $this->createMock(DadataApiInterface::class);
        $this->repository = new DadataBankRepository($this->api);
    }

    public function test_find_by_bic_returns_bank(): void
    {
        $bicValue = '044525225';

        $this->api
            ->expects($this->once())
            ->method('findBankByBic')
            ->with($bicValue)
            ->willReturn($this->fullBankData());

        $bank = $this->repository->findByBicOrFail(Bic::fromString($bicValue));

        $this->assertEquals($bicValue, $bank->id->value);
        $this->assertEquals('ПАО "СБЕРБАНК"', $bank->name);
        $this->assertEquals('СБЕРБАНК', $bank->shortName);
        $this->assertEquals($bicValue, $bank->bic->value);
        $this->assertEquals('7707083893', $bank->inn->value);
        $this->assertEquals('30101810400000000225', $bank->correspondentAccount);
        $this->assertEquals('г. Москва, ул. Вавилова, д. 19', $bank->address);
        $this->assertEquals(BankStatus::ACTIVE, $bank->status);
    }

    public function test_find_by_bic_throws_not_found_when_api_returns_null(): void
    {
        $bicValue = '044525225';

        $this->api
            ->method('findBankByBic')
            ->with($bicValue)
            ->willReturn(null);

        $this->expectException(BankNotFoundException::class);
        $this->expectExceptionMessage($bicValue);

        $this->repository->findByBicOrFail(Bic::fromString($bicValue));
    }

    public function test_find_by_bic_maps_null_fields(): void
    {
        $bicValue = '044525225';

        $this->api
            ->method('findBankByBic')
            ->willReturn([
                'bic' => $bicValue,
                'name' => ['full' => 'Банк', 'short' => 'Б'],
                'inn' => '7707083893',
            ]);

        $bank = $this->repository->findByBicOrFail(Bic::fromString($bicValue));

        $this->assertNull($bank->correspondentAccount);
        $this->assertNull($bank->address);
        $this->assertNull($bank->status);
    }

    public function test_find_by_bic_throws_external_api_exception_when_name_missing(): void
    {
        $bicValue = '044525225';

        $this->api
            ->method('findBankByBic')
            ->willReturn([
                'bic' => $bicValue,
                'name' => [],
                'inn' => '7707083893',
            ]);

        $this->expectException(ExternalApiException::class);
        $this->expectExceptionMessage('DaData API returned bank without a name');

        $this->repository->findByBicOrFail(Bic::fromString($bicValue));
    }

    public function test_find_by_bic_uses_full_with_opf_fallback(): void
    {
        $bicValue = '044525225';

        $this->api
            ->method('findBankByBic')
            ->willReturn([
                'bic' => $bicValue,
                'name' => ['full_with_opf' => 'ПАО "СБЕРБАНК"', 'short_with_opf' => 'СБЕРБАНК'],
                'inn' => '7707083893',
            ]);

        $bank = $this->repository->findByBicOrFail(Bic::fromString($bicValue));

        $this->assertEquals('ПАО "СБЕРБАНК"', $bank->name);
        $this->assertEquals('СБЕРБАНК', $bank->shortName);
    }

    public function test_find_by_bic_uses_full_as_short_fallback(): void
    {
        $bicValue = '044525225';

        $this->api
            ->method('findBankByBic')
            ->willReturn([
                'bic' => $bicValue,
                'name' => ['full' => 'Банк без краткого имени'],
                'inn' => '7707083893',
            ]);

        $bank = $this->repository->findByBicOrFail(Bic::fromString($bicValue));

        $this->assertEquals('Банк без краткого имени', $bank->name);
        $this->assertEquals('Банк без краткого имени', $bank->shortName);
    }

    private function fullBankData(): array
    {
        return [
            'bic' => '044525225',
            'name' => ['full' => 'ПАО "СБЕРБАНК"', 'short' => 'СБЕРБАНК'],
            'inn' => '7707083893',
            'correspondent_account' => '30101810400000000225',
            'address' => ['value' => 'г. Москва, ул. Вавилова, д. 19'],
            'state' => ['status' => 'ACTIVE'],
        ];
    }
}
