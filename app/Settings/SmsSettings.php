<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SmsSettings extends Settings
{
    public string $default_driver;

    public ?int $default_gateway_id;

    public string $default_sms_rate = '1500';

    public ?int $billing_currency_id = null;

    public static function group(): string
    {
        return 'sms';
    }
}
