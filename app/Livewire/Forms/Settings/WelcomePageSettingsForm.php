<?php

namespace App\Livewire\Forms\Settings;

use App\Settings\WelcomePageSettings;
use Livewire\Form;

class WelcomePageSettingsForm extends Form
{
    public string $hero_subtitle = '';

    /** @var array<int, string> */
    public array $typewriter_phrases = [];

    public int $typewriter_type_delay = 80;

    public int $typewriter_delete_delay = 40;

    public int $typewriter_pause_delay = 2000;

    /** @var array<int, array<string, string>> */
    public array $features = [];

    public function setSettings(WelcomePageSettings $settings): void
    {
        $this->hero_subtitle = $settings->hero_subtitle;
        $this->typewriter_phrases = $settings->typewriter_phrases;
        $this->typewriter_type_delay = $settings->typewriter_type_delay;
        $this->typewriter_delete_delay = $settings->typewriter_delete_delay;
        $this->typewriter_pause_delay = $settings->typewriter_pause_delay;
        $this->features = $settings->features;
    }

    public function rules(): array
    {
        return [
            'hero_subtitle' => ['required', 'string', 'max:500'],
            'typewriter_phrases' => ['required', 'array', 'min:1'],
            'typewriter_phrases.*' => ['required', 'string', 'max:255'],
            'typewriter_type_delay' => ['required', 'integer', 'min:10', 'max:1000'],
            'typewriter_delete_delay' => ['required', 'integer', 'min:10', 'max:1000'],
            'typewriter_pause_delay' => ['required', 'integer', 'min:100', 'max:10000'],
            'features' => ['required', 'array', 'min:1'],
            'features.*.title' => ['required', 'string', 'max:120'],
            'features.*.description' => ['required', 'string', 'max:500'],
            'features.*.icon' => ['required', 'string', 'max:60'],
        ];
    }

    public function store(): void
    {
        $this->validate();

        $settings = app(WelcomePageSettings::class);

        $settings->hero_subtitle = trim($this->hero_subtitle);
        $settings->typewriter_phrases = array_values(array_map(
            fn (string $phrase) => trim($phrase),
            $this->typewriter_phrases
        ));
        $settings->typewriter_type_delay = $this->typewriter_type_delay;
        $settings->typewriter_delete_delay = $this->typewriter_delete_delay;
        $settings->typewriter_pause_delay = $this->typewriter_pause_delay;
        $settings->features = array_values(array_map(
            fn (array $feature) => [
                'title' => trim($feature['title']),
                'description' => trim($feature['description']),
                'icon' => trim($feature['icon']),
            ],
            $this->features
        ));

        $settings->save();
    }
}
