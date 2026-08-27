<?php

declare(strict_types=1);

namespace App\Geocoder\Presentation\Api\Transformers;

use App\Geocoder\Application\DTO\BankData;

final class BankTransformer extends Transformer
{
    /**
     * @param mixed $data
     * @return array<string, mixed>
     */
    public function transform(mixed $data): array
    {
        if (!$data instanceof BankData) {
            throw new \InvalidArgumentException('Expected BankData');
        }

        return [
            'id' => $data->id,
            'name' => $data->name,
            'short_name' => $data->shortName,
            'bic' => $data->bic,
            'inn' => $data->inn,
            'correspondent_account' => $data->correspondentAccount,
            'address' => $data->address,
            'status' => $data->status,
            'is_active' => $data->isActive,
        ];
    }
}
