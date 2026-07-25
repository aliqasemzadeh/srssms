<?php

namespace App\Enums\Sms;

enum SmsDirectionEnum: string
{
    case Outbound = 'outbound';
    case Inbound = 'inbound';

    public function label(): string
    {
        return __('general.sms_directions.'.$this->value);
    }

    public function color(): string
    {
        return match ($this) {
            self::Outbound => 'sky',
            self::Inbound => 'teal',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $direction) => [$direction->value => $direction->label()])
            ->all();
    }
}
