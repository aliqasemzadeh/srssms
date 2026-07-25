<?php

namespace App\Livewire\Forms\Settings;

use App\Models\Sms\Gateway;
use App\Services\Sms\SmsManager;
use App\Settings\SmsSettings;
use Illuminate\Validation\Rule;
use Livewire\Form;

class SmsSettingsForm extends Form
{
    public string $default_driver = 'log';

    public ?int $default_gateway_id = null;

    public function setSettings(SmsSettings $settings): void
    {
        $this->default_driver = $settings->default_driver ?: (string) config('sms.default', 'log');
        $this->default_gateway_id = $settings->default_gateway_id;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $drivers = array_keys(app(SmsManager::class)->driverOptions());

        return [
            'default_driver' => ['required', 'string', Rule::in($drivers)],
            'default_gateway_id' => [
                'nullable',
                'integer',
                Rule::exists('sms_gateways', 'id'),
            ],
        ];
    }

    public function store(): void
    {
        if (blank($this->default_gateway_id) || (int) $this->default_gateway_id === 0) {
            $this->default_gateway_id = null;
        } else {
            $this->default_gateway_id = (int) $this->default_gateway_id;
        }

        $this->validate();

        if ($this->default_gateway_id) {
            Gateway::query()->findOrFail($this->default_gateway_id);
        }

        $settings = app(SmsSettings::class);
        $settings->default_driver = $this->default_driver;
        $settings->default_gateway_id = $this->default_gateway_id;
        $settings->save();
    }
}
