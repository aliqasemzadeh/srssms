<?php

namespace App\Services\Sms;

class SmsMessageInspector
{
    public function containsOptOut(string $text): bool
    {
        return (bool) preg_match('/لغو\s*11/u', $text);
    }

    public function isEnglish(string $text): bool
    {
        $trimmed = trim($text);

        if ($trimmed === '') {
            return false;
        }

        $first = mb_substr($trimmed, 0, 1, 'UTF-8');

        return (bool) preg_match('/^[A-Za-z]$/u', $first);
    }

    public function billingMultiplier(string $text): int
    {
        return $this->isEnglish($text) ? 2 : 1;
    }

    public function assertContainsOptOut(string $text): void
    {
        if (! $this->containsOptOut($text)) {
            throw new \RuntimeException(__('general.sms_opt_out_required'));
        }
    }
}
