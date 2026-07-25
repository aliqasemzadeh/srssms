<?php

namespace App\Enums;

enum WithdrawalStatusEnum: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Approved = 'approved';
    case Completed = 'completed';
    case Rejected = 'rejected';
    case Canceled = 'canceled';

    public function label(): string
    {
        return __('general.statuses.'.$this->value);
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
