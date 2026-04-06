<?php

declare(strict_types=1);

namespace App\Geocoder\Domain\Entities;

use App\Geocoder\Domain\Enums\BankStatus;
use App\Geocoder\Domain\ValueObjects\Bic;
use App\Geocoder\Domain\ValueObjects\Inn;

final class Bank
{
    /**
     * @param Bic $id Идентификатор банка (БИК)
     * @param string $name Полное название банка
     * @param string $shortName Краткое название банка
     * @param Bic $bic БИК банка
     * @param Inn $inn ИНН банка
     * @param string|null $correspondentAccount Корреспондентский счёт
     * @param string|null $address Адрес банка
     * @param BankStatus|null $status Статус банка
     */
    public function __construct(
        private(set) Bic $id,
        private(set) string $name,
        private(set) string $shortName,
        private(set) Bic $bic,
        private(set) Inn $inn,
        private(set) ?string $correspondentAccount = null,
        private(set) ?string $address = null,
        private(set) ?BankStatus $status = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id->value,
            'name' => $this->name,
            'short_name' => $this->shortName,
            'bic' => $this->bic->value,
            'inn' => $this->inn->value,
            'correspondent_account' => $this->correspondentAccount,
            'address' => $this->address,
            'status' => $this->status,
        ];
    }
}
