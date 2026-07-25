<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class PaymentSettings extends Settings
{
    public string $default;

    /** @var array<int, string> */
    public array $enabled;

    public array $drivers;

    public static function group(): string
    {
        return 'payment';
    }
}
