<?php

namespace App\Enums\Phonebook;

enum ContactNoteStatusEnum: string
{
    case Purchase = 'purchase';
    case Call = 'call';
    case Sms = 'sms';
    case Relation = 'relation';

    public function label(): string
    {
        return __('general.contact_note_statuses.'.$this->value);
    }

    public function color(): string
    {
        return match ($this) {
            self::Purchase => 'emerald',
            self::Call => 'sky',
            self::Sms => 'violet',
            self::Relation => 'amber',
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
