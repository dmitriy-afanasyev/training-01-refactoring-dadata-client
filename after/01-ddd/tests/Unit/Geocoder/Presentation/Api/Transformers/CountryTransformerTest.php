<?php

declare(strict_types=1);

namespace Tests\Unit\Geocoder\Presentation\Api\Transformers;

use App\Geocoder\Presentation\Api\Transformers\CountryTransformer;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(CountryTransformer::class)]
class CountryTransformerTest extends TestCase
{
    private CountryTransformer $transformer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transformer = new CountryTransformer();
    }

    public function test_transform_countries_returns_same_array(): void
    {
        $countries = ['Россия', 'Казахстан', 'Беларусь'];

        $result = $this->transformer->transform($countries);

        $this->assertEquals($countries, $result);
    }

    public function test_transform_single_country(): void
    {
        $countries = ['Россия'];

        $result = $this->transformer->transform($countries);

        $this->assertEquals($countries, $result);
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
        $this->expectExceptionMessage('Expected array of countries');

        $this->transformer->transform('not an array');
    }
}
