<?php

namespace App\Livewire\Forms\Settings;

use App\Models\Finance\Currency;
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

    public ?int $billing_currency_id = null;

    public function setSettings(SmsSettings $settings): void
    {
        $this->default_driver = $settings->default_driver ?: (string) config('sms.default', 'log');
        $this->default_gateway_id = $settings->default_gateway_id;
        $this->default_sms_rate = (string) ($settings->default_sms_rate ?: '1500');
        $this->billing_currency_id = $settings->billing_currency_id;
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
            'billing_currency_id' => [
                'required',
                'integer',
                Rule::exists('currencies', 'id')->where(
                    fn ($query) => $query->whereNull('deleted_at')->where('is_active', true)->where('type', 'fiat')
                ),
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

        if (blank($this->billing_currency_id) || (int) $this->billing_currency_id === 0) {
            $this->billing_currency_id = null;
        } else {
            $this->billing_currency_id = (int) $this->billing_currency_id;
        }

        $this->validate();

        if ($this->default_gateway_id) {
            Gateway::query()->findOrFail($this->default_gateway_id);
        }

        Currency::query()
            ->whereKey($this->billing_currency_id)
            ->where('is_active', true)
            ->where('type', 'fiat')
            ->firstOrFail();

        $settings = app(SmsSettings::class);
        $settings->default_driver = $this->default_driver;
        $settings->default_gateway_id = $this->default_gateway_id;
        $settings->default_sms_rate = (string) $this->default_sms_rate;
        $settings->billing_currency_id = $this->billing_currency_id;
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
