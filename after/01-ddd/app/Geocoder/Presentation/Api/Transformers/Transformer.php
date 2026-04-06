<?php

declare(strict_types=1);

namespace App\Geocoder\Presentation\Api\Transformers;

abstract class Transformer
{
    /**
     * @param mixed $data Данные для преобразования
     * @return array<string, mixed>|array<int, array<string, mixed>>
     */
    abstract public function transform(mixed $data): array;
}
