<?php

use App\Livewire\Concerns\AuthorizesAdministratorPermissions;
use App\Enums\Sms\SmsGatewayAccessTypeEnum;
use App\Enums\Sms\SmsGatewayUsageTypeEnum;
use App\Livewire\Forms\GatewayForm;
use App\Models\Sms\Gateway;
use App\Models\Sms\Provider;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    use AuthorizesAdministratorPermissions;

    public GatewayForm $form;

    #[On('panels.administrator.sms-management.gateway.edit.assign-data')]
    public function assignData(int $gateway): void
    {
        $this->authorizePermission('sms-management.gateway.edit');

        $this->form->setModel(Gateway::query()->findOrFail($gateway));
        $this->resetValidation();
        unset($this->providers);

        Flux::modal('sms-management.gateway.edit')->show();
    }

    #[Computed]
    public function providers(): Collection
    {
        return Provider::query()->orderBy('name')->get(['id', 'name']);
    }

    public function save(): void
    {
        $this->authorizePermission('sms-management.gateway.edit');

        $this->form->update();
        $this->dispatch('panels.administrator.sms-management.gateway.index.refresh');
        Flux::modals()->close();
        Flux::toast(__('general.sms_gateway_updated'));
    }
};
?>

<flux:modal name="sms-management.gateway.edit" flyout position="right" class="space-y-6">
    <div>
        <flux:heading size="lg">{{ __('actions.edit') }} {{ __('general.sms_gateway') }}</flux:heading>
        <flux:subheading>{{ __('general.sms_gateways') }}</flux:subheading>
    </div>

    <form wire:submit="save" class="space-y-6">
        <flux:select wire:model="form.provider_id" variant="listbox" searchable label="{{ __('general.provider') }}">
            @foreach ($this->providers as $provider)
                <flux:select.option value="{{ $provider->id }}">{{ $provider->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:input wire:model="form.title" label="{{ __('general.title') }}" icon="radio-tower" />

        <flux:input wire:model="form.number" label="{{ __('general.gateway_number') }}" icon="hash" dir="ltr" />

        <flux:input wire:model="form.sms_rate" type="number" min="0" label="{{ __('general.sms_rate') }}" description="{{ __('general.sms_rate_hint') }}" dir="ltr" />

        <flux:select wire:model="form.access_type" variant="listbox" searchable label="{{ __('general.gateway_access_type') }}">
            @foreach (SmsGatewayAccessTypeEnum::options() as $value => $label)
                <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model="form.usage_type" variant="listbox" searchable label="{{ __('general.gateway_usage_type') }}">
            @foreach (SmsGatewayUsageTypeEnum::options() as $value => $label)
                <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>

        <div class="flex items-center justify-between gap-3">
            <div>
                <flux:label>{{ __('general.is_public') }}</flux:label>
                <flux:description>{{ __('general.is_public_hint') }}</flux:description>
            </div>
            <flux:switch wire:model="form.is_public" />
        </div>

        <div class="flex items-center justify-between gap-3">
            <flux:label>{{ __('general.is_active') }}</flux:label>
            <flux:switch wire:model="form.is_active" />
        </div>

        <flux:button type="submit" variant="primary" color="orange" class="w-full">
            {{ __('actions.save') }}
        </flux:button>
    </form>
</flux:modal>
