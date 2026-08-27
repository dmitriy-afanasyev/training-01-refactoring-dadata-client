<?php

declare(strict_types=1);

namespace Tests\Unit\Geocoder\Presentation\Api\Transformers;

use App\Geocoder\Application\DTO\BankData;
use App\Geocoder\Presentation\Api\Transformers\BankTransformer;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(BankTransformer::class)]
class BankTransformerTest extends TestCase
{
    public function test_transform_bank_data(): void
    {
        $bankData = new BankData(
            id: '044525225',
            name: 'ПАО "СБЕРБАНК"',
            shortName: 'СБЕРБАНК',
            bic: '044525225',
            inn: '7707083893',
            isActive: true,
            correspondentAccount: '30101810400000000225',
            address: 'г. Москва, ул. Вавилова, д. 19',
            status: 'ACTIVE',
        );

        $transformer = new BankTransformer();
        $result = $transformer->transform($bankData);

        $this->assertEquals([
            'id' => '044525225',
            'name' => 'ПАО "СБЕРБАНК"',
            'short_name' => 'СБЕРБАНК',
            'bic' => '044525225',
            'inn' => '7707083893',
            'correspondent_account' => '30101810400000000225',
            'address' => 'г. Москва, ул. Вавилова, д. 19',
            'status' => 'ACTIVE',
            'is_active' => true,
        ], $result);
    }

    public function test_transform_bank_data_with_null_status(): void
    {
        $bankData = new BankData(
            id: '044525225',
            name: 'Банк',
            shortName: 'Б',
            bic: '044525225',
            inn: '7707083893',
            isActive: false,
        );

        $transformer = new BankTransformer();
        $result = $transformer->transform($bankData);

        $this->assertNull($result['status']);
        $this->assertFalse($result['is_active']);
    }

    public function test_transform_throws_exception_for_invalid_input(): void
    {
        $transformer = new BankTransformer();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected BankData');

        $transformer->transform('invalid_data');
    }
}
