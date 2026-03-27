<?php

declare(strict_types=1);

namespace App\Geocoder\Domain\Entities;

/**
 * Сущность: Адрес.
 */
final class Address
{
    /**
     * @param string $value Полное представление адреса
     * @param string|null $country Страна
     * @param string|null $region Регион
     * @param string|null $city Город
     * @param string|null $street Улица
     * @param string|null $house Дом
     * @param string|null $building Строение
     * @param string|null $apartment Квартира
     * @param string|null $postalCode Почтовый индекс
     */
    public function __construct(
        private(set) string $value,
        private(set) ?string $country = null,
        private(set) ?string $region = null,
        private(set) ?string $city = null,
        private(set) ?string $street = null,
        private(set) ?string $house = null,
        private(set) ?string $building = null,
        private(set) ?string $apartment = null,
        private(set) ?string $postalCode = null,
    ) {
    }

    /**
     * Получить массив данных сущности.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'country' => $this->country,
            'region' => $this->region,
            'city' => $this->city,
            'street' => $this->street,
            'house' => $this->house,
            'building' => $this->building,
            'apartment' => $this->apartment,
            'postal_code' => $this->postalCode,
        ];
    }
}
