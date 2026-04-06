<?php

declare(strict_types=1);

namespace Tests\Unit\Geocoder\Presentation\Api\Requests;

use App\Geocoder\Presentation\Api\Requests\AddressSearchRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(AddressSearchRequest::class)]
class AddressSearchRequestTest extends TestCase
{
    public function test_validation_passes(): void
    {
        $request = AddressSearchRequest::create('/test', 'GET', ['query' => 'Москва']);

        $validator = $this->app['validator']->make($request->all(), $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_when_query_missing(): void
    {
        $request = AddressSearchRequest::create('/test', 'GET', []);

        $validator = $this->app['validator']->make($request->all(), $request->rules());

        $this->assertFalse($validator->passes());
    }

    public function test_validation_fails_when_query_empty(): void
    {
        $request = AddressSearchRequest::create('/test', 'GET', ['query' => '']);

        $validator = $this->app['validator']->make($request->all(), $request->rules());

        $this->assertFalse($validator->passes());
    }

    public function test_validation_passes_with_locations(): void
    {
        $request = AddressSearchRequest::create('/test', 'GET', [
            'query' => 'Ленина',
            'locations' => ['cities' => ['Москва']],
        ]);

        $validator = $this->app['validator']->make($request->all(), $request->rules());

        $this->assertTrue($validator->passes());
    }
}
