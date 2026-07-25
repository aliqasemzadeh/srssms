<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SmsSettings extends Settings
{
    public string $default_driver;

    public ?int $default_gateway_id;

    public static function group(): string
    {
        return 'sms';
    }
}
