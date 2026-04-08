<?php

declare(strict_types=1);

namespace App\Geocoder\Presentation\Api\Transformers;

use App\Geocoder\Application\DTO\PartyData;

final class PartyTransformer extends Transformer
{
    /**
     * @param mixed $data
     * @return array<string, mixed>
     */
    public function transform(mixed $data): array
    {
        if (!$data instanceof PartyData) {
            throw new \InvalidArgumentException('Expected PartyData');
        }

        return [
            'id' => $data->id,
            'inn' => $data->inn,
            'name' => $data->name,
            'short_name' => $data->shortName,
            'address' => $data->address,
            'status' => $data->status?->value,
            'is_active' => $data->status?->isActive() ?? false,
        ];
    }
}
