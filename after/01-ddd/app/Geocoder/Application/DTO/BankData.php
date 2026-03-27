<?php

declare(strict_types=1);

namespace App\Geocoder\Application\DTO;

/**
 * DTO для данных банка.
 */
final readonly class BankData
{
    /**
     * @param string $name Полное название банка
     * @param string $shortName Краткое название банка
     * @param string $bic БИК банка
     * @param string $inn ИНН банка
     * @param string|null $correspondentAccount Корреспондентский счёт
     * @param string|null $address Адрес банка
     * @param string|null $status Статус банка
     */
    public function __construct(
        public string $name,
        public string $shortName,
        public string $bic,
        public string $inn,
        public ?string $correspondentAccount = null,
        public ?string $address = null,
        public ?string $status = null,
    ) {
    }

    /**
     * Создать DTO из массива данных.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? '',
            shortName: $data['short_name'] ?? '',
            bic: $data['bic'] ?? '',
            inn: $data['inn'] ?? '',
            correspondentAccount: $data['correspondent_account'] ?? null,
            address: $data['address'] ?? null,
            status: $data['status'] ?? null,
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
            'name' => $this->name,
            'short_name' => $this->shortName,
            'bic' => $this->bic,
            'inn' => $this->inn,
            'correspondent_account' => $this->correspondentAccount,
            'address' => $this->address,
            'status' => $this->status,
        ];
    }
}
