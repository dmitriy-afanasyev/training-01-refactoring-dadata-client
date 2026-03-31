<?php

declare(strict_types=1);

namespace App\Geocoder\UI\Http\Transformers;

use App\Geocoder\Application\DTO\BankData;
use App\Geocoder\Domain\Entities\Bank;

/**
 * Трансформер для данных банка.
 */
class BankTransformer extends Transformer
{
    /**
     * Преобразовать банк или BankData в массив.
     *
     * @param Bank|BankData $data
     * @return array<string, mixed>
     */
    public static function transform(mixed $data): array
    {
        if ($data instanceof Bank) {
            return [
                'id' => $data->getId()->value,
                'name' => $data->name,
                'short_name' => $data->shortName,
                'bic' => $data->bic->value,
                'inn' => $data->inn->value,
                'correspondent_account' => $data->correspondentAccount,
                'address' => $data->address,
                'status' => $data->status,
                'is_active' => $data->isActive(),
            ];
        }

        if ($data instanceof BankData) {
            return [
                'id' => $data->id,
                'name' => $data->name,
                'short_name' => $data->shortName,
                'bic' => $data->bic,
                'inn' => $data->inn,
                'correspondent_account' => $data->correspondentAccount,
                'address' => $data->address,
                'status' => $data->status,
            ];
        }

        throw new \InvalidArgumentException('Expected Bank or BankData');
    }
}
