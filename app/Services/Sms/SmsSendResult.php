<?php

namespace App\Services\Sms;

class SmsSendResult
{
    /**
     * @param  array<int, array{mobile: string, status: string, reference_id: ?string, error: ?string}>  $recipients
     */
    public function __construct(
        public readonly bool $success,
        public readonly array $recipients = [],
        public readonly ?string $message = null,
        public readonly mixed $raw = null,
    ) {}
}
