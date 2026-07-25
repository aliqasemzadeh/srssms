<?php

namespace App\Livewire\Forms\Settings;

use App\Settings\PaymentSettings;
use App\Support\PaymentGateways;
use Illuminate\Validation\Rule;
use Livewire\Form;

class PaymentSettingsForm extends Form
{
    public string $default = 'zarinpal';

    /** @var array<int, string> */
    public array $enabled = [];

    /** @var array<string, array<string, mixed>> */
    public array $drivers = [];

    public function setSettings(PaymentSettings $settings): void
    {
        $this->default = $settings->default ?: (string) config('payment.default', 'zarinpal');
        $this->enabled = array_values($settings->enabled ?: []);
        $this->drivers = [];

        foreach (PaymentGateways::allDrivers() as $driver) {
            $base = config("payment.drivers.{$driver}", []);
            $override = $settings->drivers[$driver] ?? [];
            $merged = array_replace_recursive($base, is_array($override) ? $override : []);

            foreach (PaymentGateways::editableKeys($driver) as $key) {
                $value = $merged[$key] ?? '';

                if (is_bool($value)) {
                    $this->drivers[$driver][$key] = $value ? '1' : '0';
                } else {
                    $this->drivers[$driver][$key] = is_scalar($value) || $value === null
                        ? (string) ($value ?? '')
                        : '';
                }
            }
        }

        if ($this->enabled === []) {
            $this->enabled = [$this->default];
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $drivers = PaymentGateways::allDrivers();

        return [
            'default' => ['required', 'string', Rule::in($drivers)],
            'enabled' => ['required', 'array', 'min:1'],
            'enabled.*' => ['required', 'string', Rule::in($drivers)],
            'drivers' => ['nullable', 'array'],
        ];
    }

    public function store(): void
    {
        $this->validate();

        if (! in_array($this->default, $this->enabled, true)) {
            $this->enabled[] = $this->default;
        }

        $this->enabled = array_values(array_unique($this->enabled));

        $cleanDrivers = [];

        foreach ($this->drivers as $driver => $fields) {
            if (! in_array($driver, PaymentGateways::allDrivers(), true) || ! is_array($fields)) {
                continue;
            }

            $clean = [];

            foreach (PaymentGateways::editableKeys($driver) as $key) {
                if (! array_key_exists($key, $fields)) {
                    continue;
                }

                $value = $fields[$key];
                $base = config("payment.drivers.{$driver}.{$key}");

                if (is_bool($base)) {
                    $clean[$key] = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
                } elseif (is_int($base) || is_float($base)) {
                    $clean[$key] = (string) $value;
                } else {
                    $clean[$key] = is_string($value) ? $value : (string) $value;
                }
            }

            if ($clean !== []) {
                $cleanDrivers[$driver] = $clean;
            }
        }

        $settings = app(PaymentSettings::class);
        $settings->default = $this->default;
        $settings->enabled = $this->enabled;
        $settings->drivers = $cleanDrivers;
        $settings->save();
    }
}
