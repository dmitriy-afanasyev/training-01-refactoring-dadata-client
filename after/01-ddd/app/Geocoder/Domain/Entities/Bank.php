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
     * @param string|null $name Полное название банка
     * @param string|null $shortName Краткое название банка
     * @param Bic $bic БИК банка
     * @param Inn $inn ИНН банка
     * @param string|null $correspondentAccount Корреспондентский счёт
     * @param string|null $address Адрес банка
     * @param BankStatus|null $status Статус банка
     */
    public function __construct(
        private(set) Bic $id,
        private(set) Bic $bic,
        private(set) Inn $inn,
        private(set) ?string $name = null,
        private(set) ?string $shortName = null,
        private(set) ?string $correspondentAccount = null,
        private(set) ?string $address = null,
        private(set) ?BankStatus $status = null,
    ) {}

    public function isActive(): bool
    {
        return $this->status?->isActive() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        //TODO: здесь и далее переделать в camelCase, так как про "формат API" домен не должен думать, и если
        // домен как-то возвращает свои данные, то в том виде в котором ему удобно, соответственно 
        // тут предсказумее вернуть в camelCase
        return [
            'id' => $this->id->value,
            'name' => $this->name,
            'short_name' => $this->shortName,
            'bic' => $this->bic->value,
            'inn' => $this->inn->value,
            'correspondent_account' => $this->correspondentAccount,
            'address' => $this->address,
            'status' => $this->status,
            'is_active' => $this->isActive(),
        ];
    }
}
