<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class PaymentSettings extends Settings
{

    public static function group(): string
    {
        return 'payment';
    }
}