<?php

namespace App\Livewire\Forms\Settings;

use App\Settings\MaintenanceSettings;
use Livewire\Form;

class MaintenanceSettingsForm extends Form
{
    public bool $is_maintenance_mode = false;

    public ?string $secret_token = null;

    public ?string $message = null;

    public function setSettings(MaintenanceSettings $settings): void
    {
        $this->is_maintenance_mode = $settings->is_maintenance_mode;
        $this->secret_token = $settings->secret_token;
        $this->message = $settings->message;
    }

    public function rules(): array
    {
        return [
            'is_maintenance_mode' => ['boolean'],
            'secret_token' => ['nullable', 'string', 'min:8', 'max:255', 'required_if:is_maintenance_mode,true'],
            'message' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function store(): MaintenanceSettings
    {
        $this->validate();

        $settings = app(MaintenanceSettings::class);

        $settings->is_maintenance_mode = $this->is_maintenance_mode;
        $settings->secret_token = $this->secret_token ?: null;
        $settings->message = $this->message ?: null;

        $settings->save();

        return $settings;
    }
}
