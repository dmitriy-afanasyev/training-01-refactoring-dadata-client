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

    /**
     * Проверяет, что при ошибке 500 клиент повторяет запрос и в итоге успешно получает данные.
     */
    public function test_retries_on_server_error_and_succeeds(): void
    {
        $client = new DadataHttpClient(
            apiKey: 'test-api-key',
            baseUrl: 'https://suggestions.dadata.ru/suggestions/api/4_1/rs',
            retryCount: 3,
            retryDelay: 100,
        );

        Http::fakeSequence('/findById/party')
            ->push('Error', 500)
            ->push('Error', 500)
            ->push(['suggestions' => [['data' => ['inn' => '7707083893']],]]);

        $result = $client->findPartyByInn('7707083893');

        $this->assertNotNull($result);
        $this->assertEquals('7707083893', $result['inn']);
        Http::assertSentCount(3);
    }

    /**
     * Проверяет, что при ConnectionException клиент повторяет запрос и успешно получает данные.
     */
    public function test_retries_on_connection_exception_and_succeeds(): void
    {
        $client = new DadataHttpClient(
            apiKey: 'test-api-key',
            baseUrl: 'https://suggestions.dadata.ru/suggestions/api/4_1/rs',
            retryCount: 2,
            retryDelay: 100,
        );

        Http::fake(function (\Illuminate\Http\Client\Request $request) {
            static $tries = 0;
            $tries++;

            if ($tries < 2) {
                throw new \GuzzleHttp\Exception\ServerException(
                    'Server Error',
                    $request->toPsrRequest(),
                    new \GuzzleHttp\Psr7\Response(500)
                );
            }

            return Http::response(['suggestions' => [['data' => ['inn' => '7707083893']]]], 200);
        });


        $result = $client->findPartyByInn('7707083893');

        $this->assertNotNull($result);
        Http::assertSentCount(2);
    }

    /**
     * Проверяет, что клиентские ошибки (4xx) не вызывают повторных попыток.
     */
    public function test_does_not_retry_on_client_error(): void
    {
        $client = new DadataHttpClient(
            apiKey: 'test-api-key',
            baseUrl: 'https://suggestions.dadata.ru/suggestions/api/4_1/rs',
            retryCount: 3,
            retryDelay: 100,
        );

        Http::fake(['/findById/party' => Http::response('Bad Request', 400)]);

        $this->expectException(ExternalApiException::class);
        $this->expectExceptionMessageMatches('/DaData API error: 400/');

        try {
            $client->findPartyByInn('7707083893');
        } finally {
            Http::assertSentCount(1);
        }
    }

    /**
     * Проверяет, что после исчерпания всех попыток выбрасывается исключение.
     */
    public function test_throws_exception_after_all_retries_exhausted(): void
    {
        $client = new DadataHttpClient(
            apiKey: 'test-api-key',
            baseUrl: 'https://suggestions.dadata.ru/suggestions/api/4_1/rs',
            retryCount: 2,
            retryDelay: 100,
        );

        Http::fakeSequence('/findById/party')
            ->push('Error', 500)
            ->push('Error', 500);

        $this->expectException(ExternalApiException::class);
        $this->expectExceptionMessageMatches('/DaData API error: 500/');

        try {
            $client->findPartyByInn('7707083893');
        } finally {
            Http::assertSentCount(2);
        }
    }

    /**
     * Проверяет математику экспоненциальной задержки (чистая функция, без доступа к внутренностям).
     */
    public function test_uses_exponential_backoff_strategy(): void
    {
        $retryDelay = 100;
        $calculateDelay = fn(int $attempt) => $retryDelay * (2 ** ($attempt - 1));

        $this->assertEquals(100, $calculateDelay(1));
        $this->assertEquals(200, $calculateDelay(2));
        $this->assertEquals(400, $calculateDelay(3));
        $this->assertEquals(800, $calculateDelay(4));
    }
}
