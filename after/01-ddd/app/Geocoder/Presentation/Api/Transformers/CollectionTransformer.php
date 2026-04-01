<?php

declare(strict_types=1);

namespace App\Geocoder\Presentation\Api\Transformers;

use Illuminate\Support\Collection;

/**
 * Трансформер для коллекций.
 */
class CollectionTransformer extends Transformer
{
    /**
     * Преобразовать коллекцию в массив.
     *
     * @param Collection|array $collection
     * @param class-string $itemTransformer Класс трансформера для элементов
     * @return array<int, array<string, mixed>>
     */
    public static function transform(
        mixed $collection,
        ?string $itemTransformer = null,
    ): array {
        if ($collection instanceof Collection) {
            $collection = $collection->all();
        }

        if (!is_array($collection)) {
            throw new \InvalidArgumentException('Expected Collection or array');
        }

        if ($itemTransformer !== null && is_a($itemTransformer, Transformer::class, true)) {
            return array_map(
                fn(mixed $item): array => $itemTransformer::transform($item),
                $collection
            );
        }

        return $collection;
    }
}
