<?php

declare(strict_types=1);

namespace App\Geocoder\Application\DTO;

final readonly class BankData
{
    /**
     * @param string $id Идентификатор банка (БИК)
     * @param string $name Полное название банка
     * @param string $shortName Краткое название банка
     * @param string $bic БИК банка
     * @param string $inn ИНН банка
     * @param bool $isActive Активен ли банк
     * @param string|null $correspondentAccount Корреспондентский счёт
     * @param string|null $address Адрес банка
     * @param string|null $status Статус банка
     */
    public function __construct(
        public string $id,
        public string $name,
        public string $shortName,
        public string $bic,
        public string $inn,
        public bool $isActive,
        public ?string $correspondentAccount = null,
        public ?string $address = null,
        public ?string $status = null,

    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? throw new \InvalidArgumentException('Bank id is required'),
            name: $data['name'] ?? '',
            shortName: $data['short_name'] ?? '',
            bic: $data['bic'] ?? '',
            inn: $data['inn'] ?? '',
            isActive: $data['is_active'] ?? throw new \InvalidArgumentException('Bank is_active is required'),
            correspondentAccount: $data['correspondent_account'] ?? null,
            address: $data['address'] ?? null,
            status: $data['status'] ?? null,
        );
    }
}
