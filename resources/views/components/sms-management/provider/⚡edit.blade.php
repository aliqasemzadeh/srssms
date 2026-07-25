<?php

use App\Livewire\Forms\ProviderForm;
use App\Models\Sms\Provider;
use App\Services\Sms\SmsManager;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public ProviderForm $form;

    #[On('panels.administrator.sms-management.provider.edit.assign-data')]
    public function assignData(int $provider): void
    {
        $this->form->setModel(Provider::query()->findOrFail($provider));
        $this->resetValidation();

        Flux::modal('sms-management.provider.edit')->show();
    }

    public function updatedFormDriver(): void
    {
        $this->form->syncCredentialFields();
    }

    public function save(): void
    {
        $this->form->update();
        $this->dispatch('panels.administrator.sms-management.provider.index.refresh');
        Flux::modals()->close();
        Flux::toast(__('general.provider_updated'));
    }
};
?>

@php
    $driverOptions = app(SmsManager::class)->driverOptions();
    $credentialKeys = app(SmsManager::class)->editableCredentialKeys($form->driver);
@endphp

<flux:modal name="sms-management.provider.edit" flyout position="right" class="space-y-6">
    <div>
        <flux:heading size="lg">{{ __('actions.edit') }} {{ __('general.provider') }}</flux:heading>
        <flux:subheading>{{ __('general.providers') }}</flux:subheading>
    </div>

    <form wire:submit="save" class="space-y-6">
        <flux:input wire:model="form.name" label="{{ __('general.name') }}" icon="building-2" />

        <flux:select wire:model.live="form.driver" variant="listbox" searchable label="{{ __('general.driver') }}">
            @foreach ($driverOptions as $value => $label)
                <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>

        @if ($credentialKeys !== [])
            <div class="space-y-4" wire:key="provider-edit-credentials-{{ $form->driver }}">
                <flux:heading size="sm">{{ __('general.credentials') }}</flux:heading>
                @foreach ($credentialKeys as $key)
                    <flux:input
                        wire:model="form.credentials.{{ $key }}"
                        label="{{ __('general.'.$key) }}"
                        type="{{ str_contains($key, 'token') || str_contains($key, 'key') ? 'password' : 'text' }}"
                        dir="ltr"
                    />
                @endforeach
            </div>
        @endif

        <div class="flex items-center justify-between gap-3">
            <flux:label>{{ __('general.is_active') }}</flux:label>
            <flux:switch wire:model="form.is_active" />
        </div>

        <flux:button type="submit" variant="primary" color="orange" class="w-full">
            {{ __('actions.save') }}
        </flux:button>
    </form>
</flux:modal>
