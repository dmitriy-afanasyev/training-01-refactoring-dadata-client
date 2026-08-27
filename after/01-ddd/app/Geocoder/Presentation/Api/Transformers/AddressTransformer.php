<?php

declare(strict_types=1);

namespace App\Geocoder\Presentation\Api\Transformers;

final class AddressTransformer extends Transformer
{
    /**
     * @param mixed $data
     * @return array<int, string>
     */
    public function transform(mixed $data): array
    {
        if (!is_array($data)) {
            throw new \InvalidArgumentException('Expected array of addresses');
        }

        return $data;
    }
}
