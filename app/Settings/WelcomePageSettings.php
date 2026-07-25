<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class WelcomePageSettings extends Settings
{

    public static function group(): string
    {
        return 'WelcomePage';
    }
}