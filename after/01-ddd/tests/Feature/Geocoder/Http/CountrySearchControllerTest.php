<?php

declare(strict_types=1);

namespace Tests\Feature\Geocoder\Http;

use App\Geocoder\Presentation\Api\Controllers\CountrySearchController;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(CountrySearchController::class)]
class CountrySearchControllerTest extends TestCase
{
    private const ENDPOINT = '/api/geocoder/country/search';

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

        $response = $this->getJson(self::ENDPOINT . '?query=Россия');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => ['Россия', 'Казахстан'],
            ]);
    }

    public function test_search_country_validation_error(): void
    {
        $response = $this->getJson(self::ENDPOINT);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error' => 'Ошибка валидации',
            ])
            ->assertJsonPath('context.errors', ['query' => ['The query field is required.']]);
    }

    public function test_search_country_external_api_error(): void
    {
        Http::fake([
            '/suggest/country' => Http::response(['error' => 'Bad Gateway'], 502),
        ]);

        $response = $this->getJson(self::ENDPOINT . '?query=Россия');

        $response->assertStatus(502)
            ->assertJson([
                'success' => false,
                'error' => 'Ошибка внешнего API',
            ]);
    }
}
