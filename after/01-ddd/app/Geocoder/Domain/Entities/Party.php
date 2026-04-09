<?php

declare(strict_types=1);

namespace App\Geocoder\Domain\Entities;

use App\Geocoder\Domain\Enums\PartyStatus;
use App\Geocoder\Domain\ValueObjects\Inn;

final class Party
{
    /**
     * @param Inn $id Идентификатор организации (ИНН)
     * @param string $name Полное название компании
     * @param string $shortName Краткое название компании
     * @param Inn $inn ИНН компании
     * @param string|null $kpp КПП компании
     * @param string|null $ogrn ОГРН компании
     * @param string|null $okpo ОКПО компании
     * @param string|null $address Адрес компании
     * @param PartyStatus|null $status Статус компании
     */
    public function __construct(
        private(set) Inn $id,
        private(set) string $name,
        private(set) string $shortName,
        private(set) Inn $inn,
        private(set) ?string $kpp = null,
        private(set) ?string $ogrn = null,
        private(set) ?string $okpo = null,
        private(set) ?string $address = null,
        private(set) ?PartyStatus $status = null,
    ) {}

    public function isActive(): bool
    {
        return $this->status?->isActive() ?? false;
    }

    /**
     * Получить массив данных сущности.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id->value,
            'name' => $this->name,
            'short_name' => $this->shortName,
            'inn' => $this->inn->value,
            'kpp' => $this->kpp,
            'ogrn' => $this->ogrn,
            'okpo' => $this->okpo,
            'address' => $this->address,
            'status' => $this->status?->value,
        ];
    }
}
