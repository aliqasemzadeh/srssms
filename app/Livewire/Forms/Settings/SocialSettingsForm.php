<?php

namespace App\Livewire\Forms\Settings;

use App\Settings\SocialSettings;
use Livewire\Form;

class SocialSettingsForm extends Form
{
    public ?string $telegram = null;

    public ?string $instagram = null;

    public ?string $linkedin = null;

    public ?string $x_twitter = null;

    public function setSettings(SocialSettings $settings): void
    {
        $this->telegram = $settings->telegram;
        $this->instagram = $settings->instagram;
        $this->linkedin = $settings->linkedin;
        $this->x_twitter = $settings->x_twitter;
    }

    public function rules(): array
    {
        return [
            'telegram' => ['nullable', 'url', 'max:255'],
            'instagram' => ['nullable', 'url', 'max:255'],
            'linkedin' => ['nullable', 'url', 'max:255'],
            'x_twitter' => ['nullable', 'url', 'max:255'],
        ];
    }

    public function store(): void
    {
        $this->validate();

        $settings = app(SocialSettings::class);

        $settings->telegram = $this->telegram ?: null;
        $settings->instagram = $this->instagram ?: null;
        $settings->linkedin = $this->linkedin ?: null;
        $settings->x_twitter = $this->x_twitter ?: null;

        $settings->save();
    }
}
