<?php

namespace App\Enums\Phonebook;

enum ContactGenderEnum: string
{
    case Male = 'male';
    case Female = 'female';
    case Other = 'other';

    public function label(): string
    {
        return __('general.contact_genders.'.$this->value);
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $gender) => [$gender->value => $gender->label()])
            ->all();
    }
}
