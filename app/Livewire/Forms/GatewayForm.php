<?php

namespace App\Livewire\Forms;

use App\Models\Sms\Gateway;
use App\Models\Sms\Provider;
use Illuminate\Validation\Rule;
use Livewire\Form;

class GatewayForm extends Form
{
    public ?Gateway $gateway = null;

    public ?int $provider_id = null;

    public string $number = '';

    public string $title = '';

    public bool $is_active = true;

    public function setModel(Gateway $gateway): void
    {
        $this->gateway = $gateway;
        $this->provider_id = $gateway->provider_id;
        $this->number = $gateway->number;
        $this->title = $gateway->title;
        $this->is_active = $gateway->is_active;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'provider_id' => ['required', 'integer', Rule::exists('sms_providers', 'id')],
            'number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('sms_gateways', 'number')
                    ->where(fn ($query) => $query->where('provider_id', $this->provider_id))
                    ->ignore($this->gateway?->id),
            ],
            'title' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ];
    }

    public function store(): Gateway
    {
        $this->validate();

        Provider::query()->findOrFail($this->provider_id);

        return Gateway::query()->create([
            'provider_id' => $this->provider_id,
            'number' => $this->number,
            'title' => $this->title,
            'is_active' => $this->is_active,
        ]);
    }

    public function update(): void
    {
        $this->validate();

        if (! $this->gateway) {
            return;
        }

        Provider::query()->findOrFail($this->provider_id);

        $this->gateway->update([
            'provider_id' => $this->provider_id,
            'number' => $this->number,
            'title' => $this->title,
            'is_active' => $this->is_active,
        ]);
    }
}
