<?php

declare(strict_types=1);

namespace Tests\Feature\Geocoder\Http;

use App\Geocoder\Presentation\Api\Controllers\BankByBicController;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(BankByBicController::class)]
class BankByBicControllerTest extends TestCase
{
    private const ENDPOINT = '/api/geocoder/bank/by-bic';

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('geocoder.api_key', 'test_api_key');
        Config::set('geocoder.base_url', 'https://suggestions.dadata.ru/suggestions/api/4_1/rs');
    }

    public function test_get_bank_by_bic_success(): void
    {
        Http::fake([
            '/suggest/bank' => Http::response([
                'suggestions' => [
                    [
                        'data' => [
                            'name' => ['full_with_opf' => 'ПАО "СБЕРБАНК"'],
                            'bic' => '044525225',
                            'inn' => '7707083893',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->postJson(self::ENDPOINT, ['bic' => '044525225']);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'bic' => '044525225',
                    'inn' => '7707083893',
                ],
            ]);
    }

    public function test_get_bank_by_bic_validation_error(): void
    {
        $response = $this->postJson(self::ENDPOINT, ['bic' => '123']);

        $response->assertStatus(422)
            ->assertJsonPath('errors.bic', ['The bic field must be 9 digits.']);
    }

    public function test_get_bank_by_bic_not_found(): void
    {
        Http::fake([
            '/suggest/bank' => Http::response([
                'suggestions' => [],
            ], 200),
        ]);

        $response = $this->postJson(self::ENDPOINT, ['bic' => '044525225']);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'error' => 'Банк не найден',
            ]);
    }

    public function test_get_bank_by_bic_external_api_error(): void
    {
        Http::fake([
            '/suggest/bank' => Http::response(['error' => 'Bad Gateway'], 502),
        ]);

        $response = $this->postJson(self::ENDPOINT, ['bic' => '044525225']);

        $response->assertStatus(502)
            ->assertJson([
                'success' => false,
                'error' => 'Ошибка внешнего API',
            ]);
    }

    public function test_get_bank_by_bic_validation_error_returns_json_without_accept_header(): void
    {
        $response = $this->withHeaders(['Accept' => 'text/html'])
            ->post(self::ENDPOINT, ['bic' => '123']);

        $response->assertStatus(422)
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonPath('errors.bic', ['The bic field must be 9 digits.']);
    }
}
