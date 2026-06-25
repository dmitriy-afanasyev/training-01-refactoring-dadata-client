<?php

declare(strict_types=1);

namespace App\Geocoder\Application\DTO;

use App\Geocoder\Domain\Enums\PartyStatus;

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
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? throw new \InvalidArgumentException('Party id is required'),
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
}
