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

            // Возможно надо оставить для наглядности

            //TODO: Подобное использование теоретически допустимо при условии что $status (PartyStatus)
            // не содержит бизнес-логики. Однако надо избегать подобных проверок `$data->status?->`
            // Так как по сути клиентский код начинает знать об особенностях строения класса - нарушается инкапсуляция.
            // См. "закон Деметры" из Чистого кода.
            // Идеальный вариант - плоские данные $data->status, $data->is_active
            'status' => $data->status?->value,

            //TODO: утечка бизнес-логики. Неявная зависимость от класса из Domain наделенного бизнес-логикой 
            // Заставить отработать бизнес-логику слоя Domain - это обязанность слоя Application
            'is_active' => $data->status?->isActive() ?? false,
        ];
    }
}
