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
        $this->assertEquals('Неверный формат ИНН', $response->getData(true)['error']);
    }

    public function test_handle_invalid_bic_exception(): void
    {
        $exception = new InvalidBicException('0445');

        $response = GeocoderExceptionHandler::handle($exception);

        $this->assertNotNull($response);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('Неверный формат БИК', $response->getData(true)['error']);
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
        $exception = new ExternalApiException('API timeout', httpStatus: 504);

        $response = GeocoderExceptionHandler::handle($exception);

        $this->assertNotNull($response);
        $this->assertEquals(502, $response->getStatusCode());
        $this->assertEquals('Ошибка внешнего API', $response->getData(true)['error']);
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

    public function test_handle_does_not_log_in_testing_environment(): void
    {
        $exception = new GeocoderException('Test error');

        Log::shouldReceive('channel')->never();

        GeocoderExceptionHandler::handle($exception);
    }
}
