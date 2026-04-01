<?php

declare(strict_types=1);

namespace App\Geocoder\Presentation\Api\Transformers;

use App\Geocoder\Application\DTO\PartyData;
use App\Geocoder\Domain\Entities\Party;

/**
 * Трансформер для данных организации.
 */
class PartyTransformer extends Transformer
{
    /**
     * Преобразовать организацию или PartyData в массив.
     *
     * @param Party|PartyData $data
     * @return array<string, mixed>
     */
    public static function transform(mixed $data): array
    {
        if ($data instanceof Party) {
            return [
                'id' => $data->id->value,
                'inn' => $data->inn->value,
                'name' => $data->name,
                'short_name' => $data->shortName,
                'address' => $data->address,
                'status' => $data->status?->value,
                'is_active' => $data->isActive(),
            ];
        }

        if ($data instanceof PartyData) {
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

        throw new \InvalidArgumentException('Expected Party or PartyData');
    }
}
