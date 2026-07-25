<?php

namespace App\Enums\Support;

enum TicketStatusEnum: string
{
    case New = 'new';
    case CustomerReply = 'customer_reply';
    case SupportReply = 'support_reply';
    case UnderReview = 'under_review';
    case Closed = 'closed';

    public function label(): string
    {
        return __('general.ticket_statuses.'.$this->value);
    }

    public function color(): string
    {
        return match ($this) {
            self::New => 'sky',
            self::CustomerReply => 'amber',
            self::SupportReply => 'indigo',
            self::UnderReview => 'violet',
            self::Closed => 'zinc',
        };
    }

    /**
     * Statuses that need admin attention.
     *
     * @return list<self>
     */
    public static function attentionCases(): array
    {
        return [self::New, self::CustomerReply];
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
