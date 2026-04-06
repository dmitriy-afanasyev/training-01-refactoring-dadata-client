<?php

declare(strict_types=1);

namespace Tests\Unit\Geocoder\Presentation\Api\Requests;

use App\Geocoder\Presentation\Api\Requests\PartyByInnRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(PartyByInnRequest::class)]
class PartyByInnRequestTest extends TestCase
{
    public function test_validation_passes_for_10_digits(): void
    {
        $request = PartyByInnRequest::create('/test', 'GET', ['inn' => '7707083893']);

        $validator = $this->app['validator']->make($request->all(), $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_for_12_digits(): void
    {
        $request = PartyByInnRequest::create('/test', 'GET', ['inn' => '770708389301']);

        $validator = $this->app['validator']->make($request->all(), $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_when_inn_too_short(): void
    {
        $request = PartyByInnRequest::create('/test', 'GET', ['inn' => '770708389']);

        $validator = $this->app['validator']->make($request->all(), $request->rules());

        $this->assertFalse($validator->passes());
    }

    public function test_validation_fails_when_inn_too_long(): void
    {
        $request = PartyByInnRequest::create('/test', 'GET', ['inn' => '7707083893123']);

        $validator = $this->app['validator']->make($request->all(), $request->rules());

        $this->assertFalse($validator->passes());
    }

    public function test_validation_fails_when_inn_contains_letters(): void
    {
        $request = PartyByInnRequest::create('/test', 'GET', ['inn' => '77070838AB']);

        $validator = $this->app['validator']->make($request->all(), $request->rules());

        $this->assertFalse($validator->passes());
    }
}
