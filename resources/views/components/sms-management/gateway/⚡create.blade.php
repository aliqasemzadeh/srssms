<?php

use App\Enums\Sms\SmsGatewayAccessTypeEnum;
use App\Enums\Sms\SmsGatewayUsageTypeEnum;
use App\Livewire\Forms\GatewayForm;
use App\Models\Sms\Provider;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public GatewayForm $form;

    #[On('panels.administrator.sms-management.gateway.create.assign-data')]
    public function assignData(): void
    {
        $this->form->reset();
        $this->form->access_type = SmsGatewayAccessTypeEnum::Shared->value;
        $this->form->usage_type = SmsGatewayUsageTypeEnum::Service->value;
        $this->form->is_public = false;
        $this->form->is_active = true;
        $this->resetValidation();
        unset($this->providers);

        Flux::modal('sms-management.gateway.create')->show();
    }

    #[Computed]
    public function providers(): Collection
    {
        return Provider::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);
    }

    public function save(): void
    {
        $this->form->store();
        $this->form->reset();
        $this->dispatch('panels.administrator.sms-management.gateway.index.refresh');
        Flux::modals()->close();
        Flux::toast(__('general.sms_gateway_created'));
    }
};
?>

<flux:modal name="sms-management.gateway.create" flyout position="right" class="space-y-6">
    <div>
        <flux:heading size="lg">{{ __('actions.create') }} {{ __('general.sms_gateway') }}</flux:heading>
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

        <flux:button type="submit" variant="primary" color="teal" class="w-full">
            {{ __('actions.save') }}
        </flux:button>
    </form>
</flux:modal>
