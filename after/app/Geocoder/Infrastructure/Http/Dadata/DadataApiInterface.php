<?php

declare(strict_types=1);

namespace App\Geocoder\Infrastructure\Http\Dadata;

use App\Geocoder\Domain\Exceptions\ExternalApiException;
use Illuminate\Http\Client\ConnectionException;

/**
 * Интерфейс для работы с DaData API.
 */
interface DadataApiInterface
{
    /**
     * Найти организацию по ИНН.
     *
     * @param string $inn ИНН организации
     *
     * @return array<string, mixed>|null
     */
    public function findPartyByInn(string $inn): ?array;

    /**
     * Найти банк по БИК.
     *
     * @param string $bic БИК банка
     *
     * @return array<string, mixed>|null
     */
    public function findBankByBic(string $bic): ?array;

    /**
     * Поиск страны.
     *
     * @param string $query Запрос для поиска
     *
     * @return array<int, string>
     */
    public function searchCountry(string $query): array;

    /**
     * Поиск адреса.
     *
     * @param string $query Запрос для поиска
     * @param array<string, mixed>|null $locations Фильтр по локациям
     *
     * @return array<int, string>
     */
    public function searchAddress(string $query, ?array $locations = null): array;
}
