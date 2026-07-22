<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SecuritySettings extends Settings
{
    public bool $is_registration_enabled;

    /** @var array<int, string> */
    public array $banned_usernames;

    /** @var array<int, string> */
    public array $banned_ips;

    public static function group(): string
    {
        return 'security';
    }
}
