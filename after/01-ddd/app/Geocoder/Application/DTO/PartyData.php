<?php

declare(strict_types=1);

namespace App\Geocoder\Application\DTO;

use App\Geocoder\Domain\Enums\PartyStatus;

/**
 * DTO для данных организации.
 */
final readonly class PartyData
{
    /**
     * @param string $id Идентификатор организации (ИНН)
     * @param string $name Полное название компании
     * @param string $shortName Краткое название компании
     * @param string $inn ИНН компании
     * @param string|null $kpp КПП компании
     * @param string|null $ogrn ОГРН компании
     * @param string|null $okpo ОКПО компании
     * @param string|null $address Адрес компании
     * @param PartyStatus|null $status Статус компании
     */
    public function __construct(
        public string $id,
        public string $name,
        public string $shortName,
        public string $inn,
        public ?string $kpp = null,
        public ?string $ogrn = null,
        public ?string $okpo = null,
        public ?string $address = null,
        public ?PartyStatus $status = null,
    ) {}

    /**
     * Создать DTO из массива данных.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'] ?? '',
            shortName: $data['short_name'] ?? '',
            inn: $data['inn'] ?? '',
            kpp: $data['kpp'] ?? null,
            ogrn: $data['ogrn'] ?? null,
            okpo: $data['okpo'] ?? null,
            address: $data['address'] ?? null,
            status: isset($data['status']) && is_string($data['status'])
                ? PartyStatus::fromString($data['status'])
                : ($data['status'] ?? null),
        );
    }

    /**
     * Преобразовать в массив.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'short_name' => $this->shortName,
            'inn' => $this->inn,
            'kpp' => $this->kpp,
            'ogrn' => $this->ogrn,
            'okpo' => $this->okpo,
            'address' => $this->address,
            'status' => $this->status?->value,
        ];
    }
}
