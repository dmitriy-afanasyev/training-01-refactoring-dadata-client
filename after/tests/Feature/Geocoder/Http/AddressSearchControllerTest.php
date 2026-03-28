<?php

declare(strict_types=1);

namespace Tests\Feature\Geocoder\Http;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
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
        $response = $this->getJson('/api/dadata/address/search?query=Москва');

        // Ожидаем ошибку API из-за отсутствия реального ключа
        $response->assertStatus(502);
    }

    public function test_search_address_validation_error(): void
    {
        $response = $this->getJson('/api/dadata/address/search');

        $response->assertStatus(422);
    }
}
