<?php

declare(strict_types=1);

namespace Tests\Feature\Geocoder\Http;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BankByBicControllerTest extends TestCase
{
    use RefreshDatabase;

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

        $response = $this->getJson(self::ENDPOINT . '?bic=044525225');

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
        $response = $this->getJson(self::ENDPOINT . '?bic=123');

        $response->assertStatus(422);
    }
}
