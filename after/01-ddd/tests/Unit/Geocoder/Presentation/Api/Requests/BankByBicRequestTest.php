<?php

declare(strict_types=1);

namespace Tests\Unit\Geocoder\Presentation\Api\Requests;

use App\Geocoder\Presentation\Api\Requests\BankByBicRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(BankByBicRequest::class)]
class BankByBicRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_validation_passes_for_valid_bic(): void
    {
        $request = BankByBicRequest::create('/test', 'GET', ['bic' => '044525225']);

        $validator = $this->app['validator']->make(
            $request->all(),
            $request->rules()
        );

        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_when_bic_missing(): void
    {
        $request = BankByBicRequest::create('/test', 'GET', []);

        $validator = $this->app['validator']->make(
            $request->all(),
            $request->rules()
        );

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('bic', $validator->errors()->messages());
    }

    public function test_validation_fails_when_bic_wrong_length(): void
    {
        $request = BankByBicRequest::create('/test', 'GET', ['bic' => '04452']);

        $validator = $this->app['validator']->make(
            $request->all(),
            $request->rules()
        );

        $this->assertFalse($validator->passes());
    }

    public function test_validation_fails_when_bic_contains_letters(): void
    {
        $request = BankByBicRequest::create('/test', 'GET', ['bic' => 'abc525225']);

        $validator = $this->app['validator']->make(
            $request->all(),
            $request->rules()
        );

        $this->assertFalse($validator->passes());
    }

    public function test_get_bic(): void
    {
        $request = BankByBicRequest::create('/test', 'GET', ['bic' => '044525225']);

        $request->setContainer($this->app);
        $request->setRedirector($this->app['redirect']);
        $request->validateResolved();

        $this->assertSame('044525225', $request->getBic());
    }
}
