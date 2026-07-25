<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class WelcomePageSettings extends Settings
{
    public string $hero_subtitle;

    /** @var array<int, string> */
    public array $typewriter_phrases;

    public int $typewriter_type_delay;

    public int $typewriter_delete_delay;

    public int $typewriter_pause_delay;

    public array $features;

    public static function group(): string
    {
        return 'welcome_page';
    }
}
