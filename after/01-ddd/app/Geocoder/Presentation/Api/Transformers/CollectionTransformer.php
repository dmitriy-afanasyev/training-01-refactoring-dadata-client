<?php

declare(strict_types=1);

namespace App\Geocoder\Presentation\Api\Transformers;

use Illuminate\Support\Collection;

final class CollectionTransformer extends Transformer
{
    /**
     * @param Collection|array $collection
     * @return array<int, array<string, mixed>>
     */
    public function transform(mixed $collection): array
    {
        if ($collection instanceof Collection) {
            $collection = $collection->all();
        }

        if (!is_array($collection)) {
            throw new \InvalidArgumentException('Expected Collection or array');
        }

        return $collection;
    }

    /**
     * Преобразовать коллекцию с применением трансформера к элементам.
     *
     * @param Collection|array $collection
     * @param Transformer $itemTransformer Трансформер для элементов
     * @return array<int, array<string, mixed>>
     */
    public function transformWith(mixed $collection, Transformer $itemTransformer): array
    {
        if ($collection instanceof Collection) {
            $collection = $collection->all();
        }

        if (!is_array($collection)) {
            throw new \InvalidArgumentException('Expected Collection or array');
        }

        return array_map(
            fn(mixed $item): array => $itemTransformer->transform($item),
            $collection
        );
    }
}
