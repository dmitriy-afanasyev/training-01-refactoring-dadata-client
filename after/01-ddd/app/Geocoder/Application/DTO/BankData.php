<?php

declare(strict_types=1);

namespace App\Geocoder\Application\DTO;

use App\Geocoder\Domain\Enums\BankStatus;

final readonly class BankData
{
    /**
     * @param string $id Идентификатор банка (БИК)
     * @param string $name Полное название банка
     * @param string $shortName Краткое название банка
     * @param string $bic БИК банка
     * @param string $inn ИНН банка
     * @param string|null $correspondentAccount Корреспондентский счёт
     * @param string|null $address Адрес банка
     * @param BankStatus|null $status Статус банка
     */
    public function __construct(
        public string $id,
        public string $name,
        public string $shortName,
        public string $bic,
        public string $inn,
        public ?string $correspondentAccount = null,
        public ?string $address = null,
        public ?BankStatus $status = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'] ?? '',
            shortName: $data['short_name'] ?? '',
            bic: $data['bic'] ?? '',
            inn: $data['inn'] ?? '',
            correspondentAccount: $data['correspondent_account'] ?? null,
            address: $data['address'] ?? null,
            status: isset($data['status']) && is_string($data['status'])
                ? BankStatus::fromString($data['status'])
                : ($data['status'] ?? null),
        );
    }
}
