<?php

namespace App\Enums\Sms;

enum SmsEncodingEnum: string
{
    case Gsm7 = 'gsm7';
    case Ucs2 = 'ucs2';

    public function label(): string
    {
        return __('general.sms_encodings.'.$this->value);
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $encoding) => [$encoding->value => $encoding->label()])
            ->all();
    }
}
