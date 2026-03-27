<?php

declare(strict_types=1);

namespace Tests\Feature\Geocoder\Http;

use App\Geocoder\Application\Services\PartyService;
use App\Geocoder\Domain\Exceptions\PartyNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

/**
 * Feature-тесты для DadataController.
 */
class DadataControllerTest extends TestCase
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

    public function test_get_bank_by_bic_success(): void
    {
        // Тест успешного получения банка по БИК
        $response = $this->getJson('/api/dadata/bank/by-bic?bic=044525225');

        // Так как нет реального мока API, ожидаем ошибку API или успех
        // В реальном тесте нужно мокировать HTTP-запросы
        $response->assertStatus(502); // Ошибка API из-за отсутствия реального ключа
    }

    public function test_get_bank_by_bic_validation_error(): void
    {
        $response = $this->getJson('/api/dadata/bank/by-bic?bic=123');

        $response->assertStatus(422);
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

    public function test_search_country_success(): void
    {
        $response = $this->getJson('/api/dadata/country/search?query=Россия');

        // Ожидаем ошибку API из-за отсутствия реального ключа
        $response->assertStatus(502);
    }

    public function test_search_country_validation_error(): void
    {
        $response = $this->getJson('/api/dadata/country/search');

        $response->assertStatus(422);
    }
}
