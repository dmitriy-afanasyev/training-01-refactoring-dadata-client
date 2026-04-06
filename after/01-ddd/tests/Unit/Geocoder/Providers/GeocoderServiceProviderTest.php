<?php

declare(strict_types=1);

namespace Tests\Unit\Geocoder\Providers;

use App\Geocoder\Domain\Repositories\AddressRepositoryInterface;
use App\Geocoder\Domain\Repositories\BankRepositoryInterface;
use App\Geocoder\Domain\Repositories\PartyRepositoryInterface;
use App\Geocoder\Infrastructure\Http\Dadata\DadataApiInterface;
use App\Geocoder\Infrastructure\Persistence\DadataAddressRepository;
use App\Geocoder\Infrastructure\Persistence\DadataBankRepository;
use App\Geocoder\Infrastructure\Persistence\DadataPartyRepository;
use App\Geocoder\Providers\GeocoderServiceProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(GeocoderServiceProvider::class)]
class GeocoderServiceProviderTest extends TestCase
{
    public function test_binds_dadata_api_interface(): void
    {
        $this->assertInstanceOf(
            DadataApiInterface::class,
            $this->app->make(DadataApiInterface::class)
        );
    }

    public function test_binds_party_repository(): void
    {
        $this->assertInstanceOf(
            PartyRepositoryInterface::class,
            $this->app->make(PartyRepositoryInterface::class)
        );
    }

    public function test_binds_bank_repository(): void
    {
        $this->assertInstanceOf(
            BankRepositoryInterface::class,
            $this->app->make(BankRepositoryInterface::class)
        );
    }

    public function test_binds_address_repository(): void
    {
        $this->assertInstanceOf(
            AddressRepositoryInterface::class,
            $this->app->make(AddressRepositoryInterface::class)
        );
    }

    public function test_repository_implementations_are_correct(): void
    {
        $partyRepo = $this->app->make(PartyRepositoryInterface::class);
        $bankRepo = $this->app->make(BankRepositoryInterface::class);
        $addressRepo = $this->app->make(AddressRepositoryInterface::class);

        $this->assertInstanceOf(DadataPartyRepository::class, $partyRepo);
        $this->assertInstanceOf(DadataBankRepository::class, $bankRepo);
        $this->assertInstanceOf(DadataAddressRepository::class, $addressRepo);
    }
}
