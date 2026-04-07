<?php

declare(strict_types=1);

namespace Tests\Unit\Geocoder\Presentation\Api\Responses;

use App\Geocoder\Presentation\Api\Responses\ApiResponseFactory;
use App\Geocoder\Presentation\Api\Transformers\Transformer;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(ApiResponseFactory::class)]
class ApiResponseFactoryTest extends TestCase
{
    public function test_success_with_data(): void
    {
        $response = ApiResponseFactory::success(['foo' => 'bar']);

        $this->assertTrue($response->toArray()['success']);
        $this->assertEquals(['foo' => 'bar'], $response->toArray()['data']);
        $this->assertArrayNotHasKey('error', $response->toArray());
    }

    public function test_success_with_transformer(): void
    {
        $transformer = new class extends Transformer {
            public function transform(mixed $data): array
            {
                return ['transformed' => $data];
            }
        };

        $response = ApiResponseFactory::success('original', $transformer);

        $this->assertTrue($response->toArray()['success']);
        $this->assertEquals(['transformed' => 'original'], $response->toArray()['data']);
    }

    public function test_error(): void
    {
        $response = ApiResponseFactory::error('Bad request', 'Invalid input', ['field' => 'name']);

        $this->assertFalse($response->toArray()['success']);
        $this->assertEquals('Bad request', $response->toArray()['error']);
        $this->assertEquals('Invalid input', $response->toArray()['message']);
        $this->assertEquals(['field' => 'name'], $response->toArray()['context']);
    }

    public function test_error_minimal(): void
    {
        $response = ApiResponseFactory::error('Something went wrong');

        $array = $response->toArray();

        $this->assertArrayNotHasKey('message', $array);
        $this->assertArrayNotHasKey('context', $array);
    }

    public function test_not_found(): void
    {
        $response = ApiResponseFactory::notFound('Not found', 'Resource missing');

        $this->assertFalse($response->toArray()['success']);
        $this->assertEquals('Not found', $response->toArray()['error']);
    }

    public function test_bad_gateway(): void
    {
        $response = ApiResponseFactory::badGateway('API error', 'Timeout');

        $this->assertFalse($response->toArray()['success']);
        $this->assertEquals('API error', $response->toArray()['error']);
    }

    public function test_internal_error(): void
    {
        $response = ApiResponseFactory::internalError('Server error', 'DB connection failed');

        $this->assertFalse($response->toArray()['success']);
        $this->assertEquals('Server error', $response->toArray()['error']);
    }

    public function test_to_response_returns_json(): void
    {
        $response = ApiResponseFactory::success(['key' => 'значение']);

        $jsonResponse = $response->toResponse();

        $this->assertEquals(200, $jsonResponse->getStatusCode());
        $this->assertEquals('application/json', $jsonResponse->headers->get('Content-Type'));
        $this->assertEquals(['success' => true, 'data' => ['key' => 'значение']], $jsonResponse->getData(true));
    }

    public function test_validation_error(): void
    {
        $errors = ['inn' => ['The inn field must be between 10 and 12 digits.']];

        $response = ApiResponseFactory::validationError('Ошибка валидации', $errors);

        $this->assertFalse($response->toArray()['success']);
        $this->assertEquals('Ошибка валидации', $response->toArray()['error']);
        $this->assertEquals($errors, $response->toArray()['context']['errors']);

        $jsonResponse = $response->toResponse();
        $this->assertEquals(422, $jsonResponse->getStatusCode());
    }

    public function test_validation_error_without_errors(): void
    {
        $response = ApiResponseFactory::validationError('Ошибка валидации');

        $array = $response->toArray();

        $this->assertFalse($array['success']);
        $this->assertEquals('Ошибка валидации', $array['error']);
        $this->assertEquals([], $array['context']['errors']);
        $this->assertEquals(422, $response->toResponse()->getStatusCode());
    }
}
