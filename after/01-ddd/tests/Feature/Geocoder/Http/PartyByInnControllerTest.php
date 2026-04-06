<?php

declare(strict_types=1);

namespace Tests\Feature\Geocoder\Http;

use App\Geocoder\Application\Services\PartyService;
use App\Geocoder\Domain\Exceptions\PartyNotFoundException;
use App\Geocoder\Presentation\Api\Controllers\PartyByInnController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(PartyByInnController::class)]
class PartyByInnControllerTest extends TestCase
{
    use RefreshDatabase;

    private const ENDPOINT = '/api/geocoder/party/by-inn';

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('geocoder.api_key', 'test_api_key');
        Config::set('geocoder.base_url', 'https://suggestions.dadata.ru/suggestions/api/4_1/rs');
    }

    public function test_get_party_by_inn_success(): void
    {
        Http::fake([
            '/findById/party' => Http::response([
                'suggestions' => [
                    [
                        'data' => [
                            'name' => ['full_with_opf' => 'ПАО "СБЕРБАНК"'],
                            'inn' => '7707083893',
                            'kpp' => '773601001',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->getJson(self::ENDPOINT . '?inn=7707083893');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'ПАО "СБЕРБАНК"',
                    'inn' => '7707083893',
                ],
            ]);
    }

    public function test_get_party_by_inn_not_found(): void
    {
        Http::fake([
            '/findById/party' => Http::response([
                'suggestions' => [],
            ], 200),
        ]);

        $response = $this->getJson(self::ENDPOINT . '?inn=7707083893');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'error' => 'Организация не найдена',
            ]);
    }

    public function test_get_party_by_inn_validation_error(): void
    {
        $response = $this->getJson(self::ENDPOINT . '?inn=123');

        $response->assertStatus(422);
    }

    public function test_get_party_by_inn_missing_inn(): void
    {
        $response = $this->getJson(self::ENDPOINT);

        $response->assertStatus(422);
    }
}
