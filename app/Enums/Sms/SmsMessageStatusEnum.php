<?php

namespace App\Enums\Sms;

enum SmsMessageStatusEnum: string
{
    case Pending = 'pending';
    case Queued = 'queued';
    case Sent = 'sent';
    case Delivered = 'delivered';
    case Failed = 'failed';
    case Received = 'received';

    public function label(): string
    {
        return __('general.sms_statuses.'.$this->value);
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'amber',
            self::Queued => 'sky',
            self::Sent => 'indigo',
            self::Delivered => 'green',
            self::Failed => 'red',
            self::Received => 'teal',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status) => [$status->value => $status->label()])
            ->all();
    }
}
