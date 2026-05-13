<?php

declare(strict_types=1);

namespace App\Geocoder\Infrastructure\Http\Dadata;

interface DadataApiInterface
{
    /**
     * @param string $inn ИНН организации
     *
     * @return array<string, mixed>|null
     */
    public function findPartyByInn(string $inn): ?array;

    /**
     * @param string $bic БИК банка
     *
     * @return array<string, mixed>|null
     */
    public function findBankByBic(string $bic): ?array;

    /**
     * @return array<int, string>
     */
    public function searchCountry(string $query): array;

    /**
     * @param array<string, mixed>|null $locations Фильтр по локациям
     *
     * @return array<int, string>
     */
    public function searchAddress(string $query, ?array $locations = null): array;
}
