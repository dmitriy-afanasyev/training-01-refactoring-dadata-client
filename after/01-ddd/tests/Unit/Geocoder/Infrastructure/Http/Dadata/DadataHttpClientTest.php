<?php

declare(strict_types=1);

namespace Tests\Unit\Geocoder\Infrastructure\Http\Dadata;

use App\Geocoder\Domain\Exceptions\ExternalApiException;
use App\Geocoder\Infrastructure\Http\Dadata\DadataHttpClient;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(DadataHttpClient::class)]
class DadataHttpClientTest extends TestCase
{
    private DadataHttpClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = new DadataHttpClient(
            apiKey: 'test-api-key',
            baseUrl: 'https://suggestions.dadata.ru/suggestions/api/4_1/rs',
            timeout: 30,
            connectTimeout: 10,
            retryCount: 0, // Отключаем retry для тестов
            retryDelay: 100,
        );
    }

    public function test_find_party_by_inn_success(): void
    {
        Http::fake([
            '/findById/party' => Http::response([
                'suggestions' => [
                    [
                        'data' => [
                            'name' => [
                                'full_with_opf' => 'ПАО "СБЕРБАНК"',
                                'short_with_opf' => 'СБЕРБАНК',
                            ],
                            'inn' => '7707083893',
                            'kpp' => '773601001',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $result = $this->client->findPartyByInn('7707083893');

        $this->assertNotNull($result);
        $this->assertEquals('ПАО "СБЕРБАНК"', $result['name']['full_with_opf']);
        $this->assertEquals('7707083893', $result['inn']);
    }

    public function test_find_party_by_inn_not_found(): void
    {
        Http::fake([
            '/findById/party' => Http::response([
                'suggestions' => [],
            ], 200),
        ]);

        $result = $this->client->findPartyByInn('7707083893');

        $this->assertNull($result);
    }

    public function test_find_bank_by_bic_success(): void
    {
        Http::fake([
            '/suggest/bank' => Http::response([
                'suggestions' => [
                    [
                        'data' => [
                            'name' => [
                                'full_with_opf' => 'ПАО "СБЕРБАНК"',
                            ],
                            'bic' => '044525225',
                            'inn' => '7707083893',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $result = $this->client->findBankByBic('044525225');

        $this->assertNotNull($result);
        $this->assertEquals('044525225', $result['bic']);
    }

    public function test_find_bank_by_bic_not_found(): void
    {
        Http::fake([
            '/suggest/bank' => Http::response([
                'suggestions' => [],
            ], 200),
        ]);

        $result = $this->client->findBankByBic('044525225');

        $this->assertNull($result);
    }

    public function test_search_country_success(): void
    {
        Http::fake([
            '/suggest/country' => Http::response([
                'suggestions' => [
                    ['value' => 'Россия'],
                    ['value' => 'Казахстан'],
                ],
            ], 200),
        ]);

        $result = $this->client->searchCountry('Россия');

        $this->assertCount(2, $result);
        $this->assertEquals('Россия', $result[0]);
        $this->assertEquals('Казахстан', $result[1]);
    }

    public function test_search_address_success(): void
    {
        Http::fake([
            '/suggest/address' => Http::response([
                'suggestions' => [
                    ['value' => 'г. Москва, ул. Вавилова, д. 19'],
                    ['value' => 'г. Москва, ул. Вавилова, д. 20'],
                ],
            ], 200),
        ]);

        $result = $this->client->searchAddress('Москва Вавилова 19');

        $this->assertCount(2, $result);
        $this->assertEquals('г. Москва, ул. Вавилова, д. 19', $result[0]);
    }

    public function test_search_address_with_locations(): void
    {
        Http::fake([
            '/suggest/address' => Http::response([
                'suggestions' => [
                    ['value' => 'г. Москва, ул. Вавилова, д. 19'],
                ],
            ], 200),
        ]);

        $result = $this->client->searchAddress(
            'Москва Вавилова 19',
            ['cities' => ['Москва']]
        );

        $this->assertCount(1, $result);
    }

    public function test_external_api_exception_on_failure(): void
    {
        Http::fake([
            '/findById/party' => Http::response('Error', 500),
        ]);

        $this->expectException(ExternalApiException::class);
        $this->expectExceptionMessageMatches('/DaData API error: 500/');

        $this->client->findPartyByInn('7707083893');
    }

    public function test_prevent_stray_requests(): void
    {
        Http::preventStrayRequests();

        $this->expectException(\Illuminate\Http\Client\StrayRequestException::class);

        $this->client->findPartyByInn('7707083893');
    }

    public function test_client_with_custom_interface(): void
    {
        $clientWithInterface = new DadataHttpClient(
            apiKey: 'test-api-key',
            baseUrl: 'https://suggestions.dadata.ru/suggestions/api/4_1/rs',
            interface: '192.168.1.100',
        );

        Http::fake([
            '/findById/party' => Http::response([
                'suggestions' => [
                    ['data' => ['inn' => '7707083893']],
                ],
            ], 200),
        ]);

        $result = $clientWithInterface->findPartyByInn('7707083893');

        $this->assertNotNull($result);
        $this->assertEquals('7707083893', $result['inn']);
    }

    public function test_find_party_returns_null_when_data_missing(): void
    {
        Http::fake([
            '/findById/party' => Http::response([
                'suggestions' => [['data' => null]],
            ], 200),
        ]);

        $result = $this->client->findPartyByInn('7707083893');

        $this->assertNull($result);
    }

    public function test_find_bank_returns_null_when_data_missing(): void
    {
        Http::fake([
            '/suggest/bank' => Http::response([
                'suggestions' => [['data' => null]],
            ], 200),
        ]);

        $result = $this->client->findBankByBic('044525225');

        $this->assertNull($result);
    }
}
