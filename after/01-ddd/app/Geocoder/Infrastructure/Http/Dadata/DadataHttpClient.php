<?php

declare(strict_types=1);

namespace App\Geocoder\Infrastructure\Http\Dadata;

use App\Geocoder\Domain\Exceptions\ExternalApiException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;

readonly class DadataHttpClient implements DadataApiInterface
{
    public function __construct(
        private string $apiKey,
        private string $baseUrl,
        private int $timeout = 40,
        private int $connectTimeout = 20,
        private int $retryCount = 3,
        private int $retryDelay = 100,
        private int $maxRedirects = 10,
        private ?string $interface = null,
    ) {}

    private function httpClient(): PendingRequest
    {
        $options = [
            'max_redirects' => $this->maxRedirects,
        ];

        // Использовать статический IP если настроен
        if ($this->interface !== null) {
            $options['curl'] = [
                CURLOPT_INTERFACE => $this->interface,
            ];
        }

        return Http::withHeaders([
            'Authorization' => 'Token ' . $this->apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])
            ->timeout($this->timeout)
            ->connectTimeout($this->connectTimeout)
            ->withOptions($options)
            ->retry($this->retryCount, $this->retryDelay, function ($exception) {
                // Boost-rule: Повторять только при ошибках соединения или сервера (5xx)
                return $exception instanceof ConnectionException
                    || ($exception instanceof RequestException
                        && $exception->response->serverError());
            })
            ->baseUrl($this->baseUrl);
    }

    /**
     * @param string $endpoint Эндпоинт относительно base_url
     * @param array<string, mixed> $payload Данные запроса
     *
     * @throws ExternalApiException
     * @throws ConnectionException
     */
    private function request(string $endpoint, array $payload): array
    {
        try {
            // Boost-rule
            $response = $this->httpClient()->post($endpoint, $payload)->throw();
        } catch (RequestException $e) {
            throw new ExternalApiException(
                sprintf(
                    'DaData API error: %d %s',
                    $e->response->status(),
                    $e->response->body()
                ),
                $e->response->status()
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
     * @see https://confluence.hflabs.ru/spaces/SGTDOC/pages/204669107/Подсказки+по+адресу+API
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
