<?php

namespace App\Enums\Sms;

enum SmsGatewayUsageTypeEnum: string
{
    case Advertising = 'advertising';
    case Service = 'service';

    public function label(): string
    {
        return __('general.sms_gateway_usage_types.'.$this->value);
    }

    public function color(): string
    {
        return match ($this) {
            self::Advertising => 'amber',
            self::Service => 'teal',
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
