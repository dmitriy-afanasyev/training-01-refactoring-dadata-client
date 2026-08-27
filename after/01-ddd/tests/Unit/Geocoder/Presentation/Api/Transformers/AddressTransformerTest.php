<?php

declare(strict_types=1);

namespace Tests\Unit\Geocoder\Presentation\Api\Transformers;

use App\Geocoder\Presentation\Api\Transformers\AddressTransformer;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(AddressTransformer::class)]
class AddressTransformerTest extends TestCase
{
    private AddressTransformer $transformer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transformer = new AddressTransformer();
    }

    public function test_transform_addresses_returns_same_array(): void
    {
        $addresses = [
            'г. Москва, ул. Вавилова, д. 19',
            'г. Казань, ул. Баумана, д. 1',
        ];

        $result = $this->transformer->transform($addresses);

        $this->assertEquals($addresses, $result);
    }

    public function test_transform_single_address(): void
    {
        $addresses = ['г. Москва, ул. Ленина, д. 1'];

        $result = $this->transformer->transform($addresses);

        $this->assertEquals($addresses, $result);
    }

    public function test_transform_empty_array(): void
    {
        $result = $this->transformer->transform([]);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_transform_throws_exception_for_invalid_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected array of addresses');

        $this->transformer->transform('not an array');
    }
}
