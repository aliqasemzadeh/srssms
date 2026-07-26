<?php

namespace App\Services\Sms;

use App\Enums\Sms\SmsMessageStatusEnum;

class SmsStatusMapper
{
    public function map(mixed $status): SmsMessageStatusEnum
    {
        $value = is_numeric($status) ? (int) $status : strtolower(trim((string) $status));

        return match ($value) {
            0, '0', 'pending' => SmsMessageStatusEnum::Pending,
            'enqueued', 'queued' => SmsMessageStatusEnum::Queued,
            1, '1', 'sent', 'success' => SmsMessageStatusEnum::Sent,
            2, '2', 'delivered', 'deliver' => SmsMessageStatusEnum::Delivered,
            3, '3', 'failed', 'error', 'undelivered' => SmsMessageStatusEnum::Failed,
            default => SmsMessageStatusEnum::Sent,
        };
    }

    public function mapToValue(mixed $status): string
    {
        return $this->map($status)->value;
    }
}
