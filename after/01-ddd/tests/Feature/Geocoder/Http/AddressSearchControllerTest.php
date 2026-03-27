<?php

declare(strict_types=1);

namespace Tests\Feature\Geocoder\Http;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Feature-тесты для AddressSearchController.
 */
class AddressSearchControllerTest extends TestCase
{
    use RefreshDatabase;

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

        $response = $this->getJson('/api/dadata/address/search?query=Москва');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => ['г. Москва, ул. Вавилова, д. 19'],
            ]);
    }

    public function test_search_address_validation_error(): void
    {
        $response = $this->getJson('/api/dadata/address/search');

        $response->assertStatus(422);
    }
}
