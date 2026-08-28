<?php

declare(strict_types=1);

namespace App\Geocoder\Infrastructure\Persistence;

use App\Geocoder\Domain\Repositories\AddressRepositoryInterface;
use App\Geocoder\Domain\ValueObjects\Address;
use App\Geocoder\Infrastructure\Http\Dadata\DadataApiInterface;

/**
 * DDD: Доменному слою не важно как хранятся данные, по этому если данные мы берем из стороннего сервиса через API,
 *  Доменный слой всё равно обращается к Репозиторию, а не создает интерфейс для
 *  API получая знания что данные где-то во вне. А откуда брать данные из БД, файла или стороннего
 *  API - это внутреннее дело репозитория. Однако от реализации функционала обращения к
 *  API абстрагируемся (DadataApiInterface) - делаем архитектурную границу.
 */
readonly class DadataAddressRepository implements AddressRepositoryInterface
{
    public function __construct(
        private DadataApiInterface $api,
    ) {}

    public function searchAddress(Address $address, ?array $locations = null): array
    {
        $values = $this->api->searchAddress($address->value, $locations);

        // DDD: Доменный слой из репозитория получает доменные сущности
        //  Слой инфраструктуры зависит от слоя Домена и может напрямую использовать сущности.
        //  Однако надо избегать вызовов бизнес логики из Доменных сущностей в слое Инфраструктуры.
        //  Слой домена не знает про слой Инфраструктуры - четкая архитектурная граница
        return array_map(
            fn(string $value): Address => Address::fromString($value),
            $values
        );
    }

    public function searchCountry(string $query): array
    {
        return $this->api->searchCountry($query);
    }
}
