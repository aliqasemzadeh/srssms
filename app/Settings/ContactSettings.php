<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ContactSettings extends Settings
{
    public ?string $address;

    /** @var array<int, string> */
    public array $phone_numbers;

    public ?string $support_email;

    public static function group(): string
    {
        return 'contact';
    }
}
