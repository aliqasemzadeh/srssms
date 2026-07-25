<?php

namespace App\Enums\Sms;

enum SmsGatewayAccessTypeEnum: string
{
    case Dedicated = 'dedicated';
    case Shared = 'shared';

    public function label(): string
    {
        return __('general.sms_gateway_access_types.'.$this->value);
    }

    public function color(): string
    {
        return match ($this) {
            self::Dedicated => 'violet',
            self::Shared => 'sky',
        };
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
