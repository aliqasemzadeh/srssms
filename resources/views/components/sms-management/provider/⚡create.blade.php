<?php

use App\Livewire\Concerns\AuthorizesAdministratorPermissions;
use App\Livewire\Forms\ProviderForm;
use App\Services\Sms\SmsManager;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    use AuthorizesAdministratorPermissions;

    public ProviderForm $form;

    #[On('panels.administrator.sms-management.provider.create.assign-data')]
    public function assignData(): void
    {
        $this->authorizePermission('sms-management.provider.create');

        $this->form->resetForCreate();
        $this->resetValidation();

        Flux::modal('sms-management.provider.create')->show();
    }

    public function updatedFormDriver(): void
    {
        $this->form->syncCredentialFields();
    }

    public function save(): void
    {
        $this->authorizePermission('sms-management.provider.create');

        $this->form->store();
        $this->form->resetForCreate();
        $this->dispatch('panels.administrator.sms-management.provider.index.refresh');
        Flux::modals()->close();
        Flux::toast(__('general.provider_created'));
    }
};
?>

@php
    $driverOptions = app(SmsManager::class)->driverOptions();
    $credentialKeys = app(SmsManager::class)->editableCredentialKeys($form->driver);
@endphp

<flux:modal name="sms-management.provider.create" flyout position="right" class="space-y-6">
    <div>
        <flux:heading size="lg">{{ __('actions.create') }} {{ __('general.provider') }}</flux:heading>
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
            <div class="space-y-4" wire:key="provider-create-credentials-{{ $form->driver }}">
                <flux:heading size="sm">{{ __('general.credentials') }}</flux:heading>
                @foreach ($credentialKeys as $key)
                    @php
                        $isSecret = str_contains($key, 'token') || str_contains($key, 'key') || str_contains($key, 'password');
                    @endphp
                    <flux:input
                        wire:model="form.credentials.{{ $key }}"
                        label="{{ __('general.'.$key) }}"
                        type="{{ $isSecret ? 'password' : 'text' }}"
                        input:class="text-left"
                        :copyable="$isSecret"
                        :clearable="$isSecret"
                        :viewable="$isSecret"
                    />
                @endforeach
            </div>
        @endif

        <div class="flex items-center justify-between gap-3">
            <flux:label>{{ __('general.is_active') }}</flux:label>
            <flux:switch wire:model="form.is_active" />
        </div>

        <flux:button type="submit" variant="primary" color="teal" class="w-full">
            {{ __('actions.save') }}
        </flux:button>
    </form>
</flux:modal>
