<?php

declare(strict_types=1);

namespace Tests\Unit\Geocoder\Presentation\Api\Transformers;

use App\Geocoder\Application\DTO\BankData;
use App\Geocoder\Presentation\Api\Transformers\BankTransformer;
use App\Geocoder\Presentation\Api\Transformers\CollectionTransformer;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(CollectionTransformer::class)]
class CollectionTransformerTest extends TestCase
{
    private CollectionTransformer $transformer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transformer = new CollectionTransformer();
    }

    public function test_transform_array(): void
    {
        $data = [['foo' => 'bar'], ['baz' => 'qux']];

        $result = $this->transformer->transform($data);

        $this->assertEquals($data, $result);
    }

    public function test_transform_collection(): void
    {
        $collection = collect(['a', 'b', 'c']);

        $result = $this->transformer->transform($collection);

        $this->assertEquals(['a', 'b', 'c'], $result);
    }

    public function test_transform_throws_exception_for_invalid_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->transformer->transform('not a collection');
    }

    public function test_transform_with_array(): void
    {
        $items = [
            new BankData(id: '001', name: 'Bank A', shortName: 'A', bic: '001', inn: '111'),
            new BankData(id: '002', name: 'Bank B', shortName: 'B', bic: '002', inn: '222'),
        ];

        $itemTransformer = new BankTransformer();
        $result = $this->transformer->transformWith($items, $itemTransformer);

        $this->assertCount(2, $result);
        $this->assertEquals('001', $result[0]['id']);
        $this->assertEquals('002', $result[1]['id']);
    }

    public function test_transform_with_collection(): void
    {
        $items = collect([
            new BankData(id: '001', name: 'Bank A', shortName: 'A', bic: '001', inn: '111'),
        ]);

        $itemTransformer = new BankTransformer();
        $result = $this->transformer->transformWith($items, $itemTransformer);

        $this->assertCount(1, $result);
        $this->assertEquals('Bank A', $result[0]['name']);
    }

    public function test_transform_with_throws_exception_for_invalid_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->transformer->transformWith('not a collection', new BankTransformer());
    }
}
