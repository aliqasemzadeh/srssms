<?php

namespace App\Enums\Sms;

enum SmsMessageSourceEnum: string
{
    case Panel = 'panel';
    case Api = 'api';

    public function label(): string
    {
        return __('general.sms_sources.'.$this->value);
    }

    public function color(): string
    {
        return match ($this) {
            self::Panel => 'zinc',
            self::Api => 'violet',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $source) => [$source->value => $source->label()])
            ->all();
    }
}
