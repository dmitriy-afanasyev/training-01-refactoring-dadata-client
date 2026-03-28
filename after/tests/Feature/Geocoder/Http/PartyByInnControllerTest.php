<?php

declare(strict_types=1);

namespace Tests\Feature\Geocoder\Http;

use App\Geocoder\Application\Services\PartyService;
use App\Geocoder\Domain\Exceptions\PartyNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

/**
 * Feature-тесты для PartyByInnController.
 */
class PartyByInnControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Устанавливаем тестовый API ключ
        Config::set('geocoder.api_key', 'test_api_key');
        Config::set('geocoder.base_url', 'https://suggestions.dadata.ru/suggestions/api/4_1/rs');
    }

    public function test_get_party_by_inn_success(): void
    {
        // Мокаем сервис для успешного ответа
        $mockPartyData = new \App\Geocoder\Application\DTO\PartyData(
            name: 'ПАО "СБЕРБАНК"',
            shortName: 'СБЕРБАНК',
            inn: '7707083893',
            kpp: '773601001',
            status: 'ACTIVE',
        );

        $partyServiceMock = $this->createMock(PartyService::class);
        $partyServiceMock
            ->method('findByInn')
            ->with('7707083893')
            ->willReturn($mockPartyData);

        $this->app->instance(PartyService::class, $partyServiceMock);

        $response = $this->getJson('/api/dadata/party/by-inn?inn=7707083893');

        $response
            ->assertStatus(200)
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
        $partyServiceMock = $this->createMock(PartyService::class);
        $partyServiceMock
            ->method('findByInn')
            ->willThrowException(new PartyNotFoundException('Организация не найдена'));

        $this->app->instance(PartyService::class, $partyServiceMock);

        $response = $this->getJson('/api/dadata/party/by-inn?inn=7707083893');

        $response
            ->assertStatus(404)
            ->assertJson([
                'success' => false,
                'error' => 'Организация не найдена',
            ]);
    }

    public function test_get_party_by_inn_validation_error(): void
    {
        // Тест с невалидным ИНН (не 10 символов)
        $response = $this->getJson('/api/dadata/party/by-inn?inn=123');

        $response->assertStatus(422);
    }

    public function test_get_party_by_inn_missing_inn(): void
    {
        $response = $this->getJson('/api/dadata/party/by-inn');

        $response->assertStatus(422);
    }
}
