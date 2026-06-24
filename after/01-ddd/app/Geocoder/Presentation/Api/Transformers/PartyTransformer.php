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
            //TODO: утечка бизнес-логики. Неявная зависимость от PartyStatus из Domain
            // Можно оставить как пример того что AI зная правила DDD нарушает их и делает это не заметно
            // и не за один раз - по этим файлам он ходил много раз и тестами покрывал.
            // см. PartyDataTest::test_create_from_array()
            // Как должно быть? 
            // При создании DTO в Application надо было взять данные в том виде в котором они 
            // нужны слою Presentation и не оставить следов 
            'status' => $data->status?->value,
            //TODO: утечка бизнес-логики. Неявная зависимость от enum из Domain
            // Заставили отработать бизнес-логику слоя Domain - это обязанность слоя Application
            'is_active' => $data->status?->isActive() ?? false,
        ];
    }
}
