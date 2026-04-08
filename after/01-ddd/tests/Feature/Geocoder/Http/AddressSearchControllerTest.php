<?php

declare(strict_types=1);

namespace Tests\Feature\Geocoder\Http;

use App\Geocoder\Presentation\Api\Controllers\AddressSearchController;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(AddressSearchController::class)]
class AddressSearchControllerTest extends TestCase
{
    private const ENDPOINT = '/api/geocoder/address/search';

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('geocoder.api_key', 'test_api_key');
        Config::set('geocoder.base_url', 'https://suggestions.dadata.ru/suggestions/api/4_1/rs');
    }

    public function test_search_address_success(): void
    {
        Http::fake([
            '/suggest/address' => Http::response([
                'suggestions' => [
                    ['value' => 'г. Москва, ул. Вавилова, д. 19'],
                ],
            ], 200),
        ]);

        $response = $this->getJson(self::ENDPOINT . '?query=Москва');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => ['г. Москва, ул. Вавилова, д. 19'],
            ]);
    }

    public function test_search_address_validation_error(): void
    {
        $response = $this->getJson(self::ENDPOINT);

        $response->assertStatus(422)
            ->assertJsonPath('errors.query', ['The query field is required.']);
    }

    public function test_search_address_external_api_error(): void
    {
        Http::fake([
            '/suggest/address' => Http::response(['error' => 'Bad Gateway'], 502),
        ]);

        $response = $this->getJson(self::ENDPOINT . '?query=Москва');

        $response->assertStatus(502)
            ->assertJson([
                'success' => false,
                'error' => 'Ошибка внешнего API',
            ]);
    }

    public function test_search_address_validation_error_returns_json_without_accept_header(): void
    {
        $response = $this->withHeaders(['Accept' => 'text/html'])
            ->get(self::ENDPOINT);

        $response->assertStatus(422)
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonPath('errors.query', ['The query field is required.']);
    }
}
