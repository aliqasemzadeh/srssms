<?php

namespace App\Livewire\Forms;

use App\Enums\Sms\SmsGatewayAccessTypeEnum;
use App\Enums\Sms\SmsGatewayUsageTypeEnum;
use App\Models\Sms\Gateway;
use App\Models\Sms\Provider;
use App\Settings\SmsSettings;
use Illuminate\Validation\Rule;
use Livewire\Form;

class GatewayForm extends Form
{
    public ?Gateway $gateway = null;

    public ?int $provider_id = null;

    public string $number = '';

    public string $title = '';

    public string $access_type = 'shared';

    public string $usage_type = 'service';

    public bool $is_public = false;

    public bool $is_active = true;

    public string $sms_rate = '1500';

    public function setModel(Gateway $gateway): void
    {
        $this->gateway = $gateway;
        $this->provider_id = $gateway->provider_id;
        $this->number = $gateway->number;
        $this->title = $gateway->title;
        $this->access_type = $gateway->access_type->value;
        $this->usage_type = $gateway->usage_type->value;
        $this->is_public = $gateway->is_public;
        $this->is_active = $gateway->is_active;
        $this->sms_rate = (string) ($gateway->sms_rate ?: app(SmsSettings::class)->default_sms_rate);
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
            'access_type' => ['required', 'string', Rule::enum(SmsGatewayAccessTypeEnum::class)],
            'usage_type' => ['required', 'string', Rule::enum(SmsGatewayUsageTypeEnum::class)],
            'is_public' => ['boolean'],
            'is_active' => ['boolean'],
            'sms_rate' => ['required', 'integer', 'min:0'],
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
            'access_type' => $this->access_type,
            'usage_type' => $this->usage_type,
            'is_public' => $this->is_public,
            'is_active' => $this->is_active,
            'sms_rate' => (int) $this->sms_rate,
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
            'access_type' => $this->access_type,
            'usage_type' => $this->usage_type,
            'is_public' => $this->is_public,
            'is_active' => $this->is_active,
            'sms_rate' => (int) $this->sms_rate,
        ]);
    }
}
