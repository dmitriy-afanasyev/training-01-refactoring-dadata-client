<?php

declare(strict_types=1);

namespace Tests\Unit\Geocoder\Presentation\Api\Exceptions;

use App\Geocoder\Domain\Exceptions\BankNotFoundException;
use App\Geocoder\Domain\Exceptions\ExternalApiException;
use App\Geocoder\Domain\Exceptions\GeocoderException;
use App\Geocoder\Domain\Exceptions\InvalidBicException;
use App\Geocoder\Domain\Exceptions\InvalidInnException;
use App\Geocoder\Domain\Exceptions\PartyNotFoundException;
use App\Geocoder\Presentation\Api\Exceptions\GeocoderExceptionHandler;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(GeocoderExceptionHandler::class)]
class GeocoderExceptionHandlerTest extends TestCase
{
    public function test_handle_invalid_inn_exception(): void
    {
        $exception = new InvalidInnException('12345');

        $response = GeocoderExceptionHandler::handle($exception);

        $this->assertNotNull($response);
        $this->assertEquals(400, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertEquals('Неверный формат ИНН', $data['error']);
        $this->assertStringContainsString('12345', $data['message']);
        $this->assertEquals(['inn' => '12345'], $data['context']);
    }

    public function test_handle_invalid_bic_exception(): void
    {
        $exception = new InvalidBicException('0445');

        $response = GeocoderExceptionHandler::handle($exception);

        $this->assertNotNull($response);
        $this->assertEquals(400, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertEquals('Неверный формат БИК', $data['error']);
        $this->assertStringContainsString('0445', $data['message']);
    }

    public function test_handle_party_not_found_exception(): void
    {
        $exception = new PartyNotFoundException('7707083893');

        $response = GeocoderExceptionHandler::handle($exception);

        $this->assertNotNull($response);
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('Организация не найдена', $response->getData(true)['error']);
    }

    public function test_handle_bank_not_found_exception(): void
    {
        $exception = new BankNotFoundException('044525225');

        $response = GeocoderExceptionHandler::handle($exception);

        $this->assertNotNull($response);
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('Банк не найден', $response->getData(true)['error']);
    }

    public function test_handle_external_api_exception(): void
    {
        $exception = new ExternalApiException('API timeout', 504);

        $response = GeocoderExceptionHandler::handle($exception);

        $this->assertNotNull($response);
        $this->assertEquals(502, $response->getStatusCode());
        $this->assertEquals('Ошибка внешнего API', $response->getData(true)['error']);
        $this->assertEquals(['http_status' => 504], $response->getData(true)['context']);
    }

    public function test_handle_geocoder_exception(): void
    {
        $exception = new GeocoderException('Unknown error');

        $response = GeocoderExceptionHandler::handle($exception);

        $this->assertNotNull($response);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals('Ошибка модуля Geocoder', $response->getData(true)['error']);
    }

    public function test_handle_returns_null_for_unknown_exception(): void
    {
        $exception = new \RuntimeException('Some error');

        $this->assertNull(GeocoderExceptionHandler::handle($exception));
    }

    public function test_handle_validation_exception(): void
    {
        $validator = validator(['inn' => '7707083d'], ['inn' => ['required', 'string', 'digits_between:10,12']]);
        $exception = ValidationException::withMessages($validator->errors()->getMessages());

        $response = GeocoderExceptionHandler::handle($exception);

        $this->assertNotNull($response);
        $this->assertEquals(422, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertEquals('Ошибка валидации', $data['error']);
        $this->assertArrayHasKey('errors', $data['context']);
        $this->assertArrayHasKey('inn', $data['context']['errors']);
    }

    public function test_handle_logs_geocoder_exception(): void
    {
        $exception = new GeocoderException('Test error');

        $logger = \Mockery::mock(\Psr\Log\LoggerInterface::class);
        $logger->shouldReceive('error')
            ->once()
            ->with(\Mockery::type('string'), \Mockery::on(function (array $context) {
                return $context['exception']['class'] === GeocoderException::class
                    && $context['exception']['context'] === [];
            }));

        Log::shouldReceive('channel')
            ->with(GeocoderExceptionHandler::LOG_CHANNEL)
            ->once()
            ->andReturn($logger);

        GeocoderExceptionHandler::handle($exception);
    }

    public function test_handle_logs_validation_exception(): void
    {
        $validator = validator(['inn' => 'invalid'], ['inn' => ['required', 'string']]);
        $exception = ValidationException::withMessages($validator->errors()->getMessages());

        Log::shouldReceive('channel')
            ->with(null)
            ->once()
            ->andReturnSelf();
        Log::shouldReceive('error')
            ->once()
            ->with(\Mockery::type('string'), \Mockery::on(function (array $context) {
                return isset($context['validation_errors']);
            }));

        GeocoderExceptionHandler::handle($exception);
    }

    public function test_handle_logs_external_api_exception(): void
    {
        $exception = new ExternalApiException('API timeout', 504);

        $logger = \Mockery::mock(\Psr\Log\LoggerInterface::class);
        $logger->shouldReceive('error')
            ->once()
            ->with('API timeout', \Mockery::on(function (array $context) {
                return $context['exception']['class'] === ExternalApiException::class
                    && $context['exception']['context'] === ['http_status' => 504];
            }));

        Log::shouldReceive('channel')
            ->with(GeocoderExceptionHandler::LOG_CHANNEL)
            ->once()
            ->andReturn($logger);

        GeocoderExceptionHandler::handle($exception);
    }
}
