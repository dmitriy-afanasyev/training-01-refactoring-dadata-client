<?php

declare(strict_types=1);

namespace App\Geocoder\Presentation\Api\Transformers;

use App\Geocoder\Application\DTO\PartyData;

/**
 * Трансформер для данных организации.
 */
final class PartyTransformer extends Transformer
{
    /**
     * Преобразовать PartyData в массив для API-ответа.
     *
     * @param mixed $data
     * @return array<string, mixed>
     */
    public function transform(mixed $data): array
    {
        assert($data instanceof PartyData);

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
