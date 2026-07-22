<?php

namespace App\Livewire\Forms\Settings;

use App\Settings\SecuritySettings;
use Livewire\Form;

class SecuritySettingsForm extends Form
{
    public bool $is_registration_enabled = true;

    /** @var array<int, string> */
    public array $banned_usernames = [];

    /** @var array<int, string> */
    public array $banned_ips = [];

    public function setSettings(SecuritySettings $settings): void
    {
        $this->is_registration_enabled = $settings->is_registration_enabled;
        $this->banned_usernames = $settings->banned_usernames;
        $this->banned_ips = $settings->banned_ips;
    }

    public function rules(): array
    {
        return [
            'is_registration_enabled' => ['boolean'],
            'banned_usernames' => ['array'],
            'banned_usernames.*' => ['string', 'max:255'],
            'banned_ips' => ['array'],
            'banned_ips.*' => ['ip'],
        ];
    }

    public function store(): void
    {
        $this->validate();

        $settings = app(SecuritySettings::class);

        $settings->is_registration_enabled = $this->is_registration_enabled;
        $settings->banned_usernames = array_values($this->banned_usernames);
        $settings->banned_ips = array_values($this->banned_ips);

        $settings->save();
    }
}
