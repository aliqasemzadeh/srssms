<?php

namespace App\Services\Sms;

use App\Enums\Sms\SmsEncodingEnum;

class SmsPartCounter
{
    /**
     * GSM 7-bit basic character set (simplified).
     */
    private const string GSM7_CHARS = "@£\$¥èéùìòÇ\nØø\rÅåΔ_ΦΓΛΩΠΨΣΘΞÆæßÉ !\"#¤%&'()*+,-./0123456789:;<=>?¡ABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÑÜ§¿abcdefghijklmnopqrstuvwxyzäöñüà";

    /**
     * @return array{encoding: SmsEncodingEnum, parts_count: int, length: int}
     */
    public function analyze(string $text): array
    {
        $encoding = $this->detectEncoding($text);
        $length = mb_strlen($text, 'UTF-8');

        if ($encoding === SmsEncodingEnum::Gsm7) {
            $single = 160;
            $multi = 153;
        } else {
            $single = 70;
            $multi = 67;
        }

        $parts = $length === 0 ? 1 : ($length <= $single ? 1 : (int) ceil($length / $multi));

        return [
            'encoding' => $encoding,
            'parts_count' => max(1, $parts),
            'length' => $length,
        ];
    }

    public function detectEncoding(string $text): SmsEncodingEnum
    {
        $chars = mb_str_split($text, 1, 'UTF-8');

        foreach ($chars as $char) {
            if (! str_contains(self::GSM7_CHARS, $char) && $char !== "\n" && $char !== "\r") {
                return SmsEncodingEnum::Ucs2;
            }
        }

        return SmsEncodingEnum::Gsm7;
    }
}
