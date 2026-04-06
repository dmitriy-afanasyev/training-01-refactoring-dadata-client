<?php

declare(strict_types=1);

namespace Tests\Unit\Geocoder\Presentation\Api\Requests;

use App\Geocoder\Presentation\Api\Requests\CountrySearchRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(CountrySearchRequest::class)]
class CountrySearchRequestTest extends TestCase
{
    public function test_validation_passes(): void
    {
        $request = CountrySearchRequest::create('/test', 'GET', ['query' => 'Россия']);

        $validator = $this->app['validator']->make($request->all(), $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_when_query_missing(): void
    {
        $request = CountrySearchRequest::create('/test', 'GET', []);

        $validator = $this->app['validator']->make($request->all(), $request->rules());

        $this->assertFalse($validator->passes());
    }

    public function test_get_query(): void
    {
        $response = $this->getJson('/api/geocoder/country/search?query=Россия');

        $this->assertNotEquals(422, $response->getStatusCode());
    }
}
