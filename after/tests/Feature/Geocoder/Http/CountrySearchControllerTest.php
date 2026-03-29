<?php

declare(strict_types=1);

namespace Tests\Feature\Geocoder\Http;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Feature-тесты для CountrySearchController.
 */
class CountrySearchControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('geocoder.api_key', 'test_api_key');
        Config::set('geocoder.base_url', 'https://suggestions.dadata.ru/suggestions/api/4_1/rs');
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

        $response = $this->getJson('/api/dadata/country/search?query=Россия');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => ['Россия', 'Казахстан'],
            ]);
    }

    public function test_search_country_validation_error(): void
    {
        $response = $this->getJson('/api/dadata/country/search');

        $response->assertStatus(422);
    }
}
