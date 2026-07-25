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

    public string $default_sms_rate = '1500';

    public function setSettings(SmsSettings $settings): void
    {
        $this->default_driver = $settings->default_driver ?: (string) config('sms.default', 'log');
        $this->default_gateway_id = $settings->default_gateway_id;
        $this->default_sms_rate = (string) ($settings->default_sms_rate ?: '1500');
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
            'default_sms_rate' => ['required', 'integer', 'min:0'],
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
        $settings->default_sms_rate = (string) $this->default_sms_rate;
        $settings->save();
    }

    public function applyRateToAllGateways(): int
    {
        $this->validate([
            'default_sms_rate' => ['required', 'integer', 'min:0'],
        ]);

        $rate = (int) $this->default_sms_rate;

        $settings = app(SmsSettings::class);
        $settings->default_sms_rate = (string) $rate;
        $settings->save();

        return Gateway::query()->update(['sms_rate' => $rate]);
    }
}
