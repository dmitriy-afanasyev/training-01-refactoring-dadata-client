<?php

declare(strict_types=1);

namespace App\Geocoder\Infrastructure\Http\Dadata;

use App\Geocoder\Domain\Exceptions\ExternalApiException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;

/**
 * HTTP-клиент для работы с DaData API.
 */
readonly class DadataHttpClient implements DadataApiInterface
{
    /**
     * @param string $apiKey API ключ
     * @param string $baseUrl Базовый URL API
     * @param int $timeout Таймаут запроса в секундах
     */
    public function __construct(
        private string $apiKey,
        private string $baseUrl,
        private int $timeout = 30,
    ) {
    }

    /**
     * Создать HTTP-клиент с необходимыми заголовками.
     */
    private function httpClient(): PendingRequest
    {
        return Http::withHeaders([
            'Authorization' => 'Token ' . $this->apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])
            ->timeout($this->timeout)
            ->baseUrl($this->baseUrl);
    }

    /**
     * Выполнить POST-запрос к API.
     *
     * @param string $endpoint Эндпоинт относительно base_url
     * @param array<string, mixed> $payload Данные запроса
     *
     * @throws ExternalApiException
     * @throws ConnectionException
     */
    private function request(string $endpoint, array $payload): array
    {
        $response = $this->httpClient()->post($endpoint, $payload);

        if ($response->failed()) {
            throw new ExternalApiException(
                sprintf(
                    'DaData API error: %d %s',
                    $response->status(),
                    $response->body()
                ),
                $response->status()
            );
        }

        return $response->json() ?? [];
    }

    /**
     * @throws ExternalApiException
     * @throws ConnectionException
     */
    public function findPartyByInn(string $inn): ?array
    {
        $result = $this->request('/findById/party', [
            'query' => $inn,
            'branch_type' => 'MAIN',
        ]);

        $suggestions = $result['suggestions'] ?? [];

        if (empty($suggestions)) {
            return null;
        }

        return $suggestions[0]['data'] ?? null;
    }

    /**
     * @throws ExternalApiException
     * @throws ConnectionException
     */
    public function findBankByBic(string $bic): ?array
    {
        $result = $this->request('/suggest/bank', [
            'query' => $bic,
        ]);

        $suggestions = $result['suggestions'] ?? [];

        if (empty($suggestions)) {
            return null;
        }

        return $suggestions[0]['data'] ?? null;
    }

    /**
     * @throws ExternalApiException
     * @throws ConnectionException
     */
    public function searchCountry(string $query): array
    {
        $result = $this->request('/suggest/country', [
            'query' => $query,
        ]);

        $suggestions = $result['suggestions'] ?? [];

        return array_map(
            fn(array $item): string => $item['value'] ?? '',
            $suggestions
        );
    }

    /**
     * @throws ExternalApiException
     * @throws ConnectionException
     */
    public function searchAddress(string $query, ?array $locations = null): array
    {
        $payload = ['query' => $query];

        if ($locations !== null) {
            $payload['locations'] = $locations;
        }

        $result = $this->request('/suggest/address', $payload);

        $suggestions = $result['suggestions'] ?? [];

        return array_map(
            fn(array $item): string => $item['value'] ?? '',
            $suggestions
        );
    }
}
