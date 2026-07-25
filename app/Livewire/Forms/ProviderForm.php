<?php

namespace App\Livewire\Forms;

use App\Models\Sms\Provider;
use App\Services\Sms\SmsManager;
use Illuminate\Validation\Rule;
use Livewire\Form;

class ProviderForm extends Form
{
    public ?Provider $provider = null;

    public string $name = '';

    public string $driver = 'log';

    /** @var array<string, string> */
    public array $credentials = [];

    public bool $is_active = true;

    public function setModel(Provider $provider): void
    {
        $this->provider = $provider;
        $this->name = $provider->name;
        $this->driver = $provider->driver;
        $this->is_active = $provider->is_active;
        $this->credentials = [];

        foreach (app(SmsManager::class)->editableCredentialKeys($provider->driver) as $key) {
            $this->credentials[$key] = (string) ($provider->credential($key, '') ?? '');
        }
    }

    public function resetForCreate(string $driver = 'log'): void
    {
        $this->reset();
        $this->provider = null;
        $this->driver = $driver;
        $this->is_active = true;
        $this->syncCredentialFields();
    }

    public function syncCredentialFields(): void
    {
        $keys = app(SmsManager::class)->editableCredentialKeys($this->driver);
        $current = $this->credentials;
        $this->credentials = [];

        foreach ($keys as $key) {
            $this->credentials[$key] = (string) ($current[$key] ?? '');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $drivers = array_keys(app(SmsManager::class)->driverOptions());

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'driver' => ['required', 'string', Rule::in($drivers)],
            'is_active' => ['boolean'],
            'credentials' => ['nullable', 'array'],
        ];

        foreach (app(SmsManager::class)->editableCredentialKeys($this->driver) as $key) {
            $rules["credentials.{$key}"] = ['nullable', 'string', 'max:500'];
        }

        return $rules;
    }

    public function store(): Provider
    {
        $this->validate();

        return Provider::query()->create([
            'name' => $this->name,
            'driver' => $this->driver,
            'credentials' => $this->cleanCredentials(),
            'is_active' => $this->is_active,
        ]);
    }

    public function update(): void
    {
        $this->validate();

        if (! $this->provider) {
            return;
        }

        $this->provider->update([
            'name' => $this->name,
            'driver' => $this->driver,
            'credentials' => $this->cleanCredentials(),
            'is_active' => $this->is_active,
        ]);
    }

    /**
     * @return array<string, string>
     */
    protected function cleanCredentials(): array
    {
        $clean = [];

        foreach (app(SmsManager::class)->editableCredentialKeys($this->driver) as $key) {
            $clean[$key] = trim((string) ($this->credentials[$key] ?? ''));
        }

        return $clean;
    }
}
