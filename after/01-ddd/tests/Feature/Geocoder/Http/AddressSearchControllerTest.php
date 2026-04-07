<?php

declare(strict_types=1);

namespace Tests\Feature\Geocoder\Http;

use App\Geocoder\Presentation\Api\Controllers\AddressSearchController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(AddressSearchController::class)]
class AddressSearchControllerTest extends TestCase
{
    use RefreshDatabase;

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
            ->assertJson([
                'success' => false,
                'error' => 'Ошибка валидации',
            ])
            ->assertJsonPath('context.errors', ['query' => ['The query field is required.']]);
    }
}
