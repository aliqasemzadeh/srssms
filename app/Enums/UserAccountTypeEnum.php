<?php

namespace App\Enums;

enum UserAccountTypeEnum: string
{
    case Iban = 'iban';
    case Card = 'card';
    case Crypto = 'crypto';

    public function label(): string
    {
        return __('general.account_types.'.$this->value);
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type) => [$type->value => $type->label()])
            ->all();
    }
}
