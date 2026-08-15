<?php

namespace App\Support;

use Illuminate\Support\Facades\Validator;

class MobileNumber
{
    /**
     * Normalize Iranian mobile numbers to 09xxxxxxxxx.
     */
    public static function normalize(string $value): string
    {
        $value = strtr($value, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);

        $digits = preg_replace('/\D+/', '', $value) ?: '';

        if (str_starts_with($digits, '0098')) {
            $digits = substr($digits, 4);
        } elseif (str_starts_with($digits, '098')) {
            $digits = substr($digits, 3);
        } elseif (str_starts_with($digits, '98') && strlen($digits) >= 12) {
            $digits = substr($digits, 2);
        }

        if (strlen($digits) === 10 && str_starts_with($digits, '9')) {
            $digits = '0'.$digits;
        }

        return $digits;
    }

    public static function isValid(string $value): bool
    {
        $normalized = self::normalize($value);

        if ($normalized === '') {
            return false;
        }

        return ! Validator::make(
            ['mobile' => $normalized],
            ['mobile' => ['required', 'string', 'ir_mobile']]
        )->fails();
    }
}
