<?php

namespace App\Enums\Phonebook;

enum ContactPersonTypeEnum: string
{
    case Individual = 'individual';
    case Legal = 'legal';

    public function label(): string
    {
        return __('general.contact_person_types.'.$this->value);
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type) => [$type->value => $type->label()])
            ->all();
    }
}
