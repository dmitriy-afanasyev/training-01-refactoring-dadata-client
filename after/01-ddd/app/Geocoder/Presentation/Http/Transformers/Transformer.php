<?php

declare(strict_types=1);

namespace App\Geocoder\Presentation\Http\Transformers;

/**
 * Базовый класс для трансформеров.
 */
abstract class Transformer
{
    /**
     * Преобразовать данные в массив.
     *
     * @param mixed $data Данные для преобразования
     * @return array<string, mixed>|array<int, array<string, mixed>>
     */
    abstract public static function transform(mixed $data): array;
}
