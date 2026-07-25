<?php

use App\Livewire\Forms\Settings\PaymentSettingsForm;
use App\Settings\PaymentSettings;
use App\Support\PaymentGateways;
use Flux\Flux;
use Livewire\Component;

new class extends Component
{
    public PaymentSettingsForm $form;

    public string $selectedDriver = 'zarinpal';

    public function mount(PaymentSettings $settings): void
    {
        $this->form->setSettings($settings);
        $this->selectedDriver = $this->form->default ?: (PaymentGateways::allDrivers()[0] ?? 'zarinpal');
    }

    public function save(): void
    {
        $this->form->store();

        Flux::toast(__('general.settings_saved'));
    }
};
?>

@php
    $driverOptions = PaymentGateways::driverOptions();
    $editableKeys = PaymentGateways::editableKeys($selectedDriver);
@endphp

<div>
    <x-slot name="title">{{ __('general.payment_settings') }} - {{ config('app.name') }}</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('panels.administrator.dashboard.index') }}" icon="home" wire:navigate />
                <flux:breadcrumbs.item>{{ __('general.finance_management') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('panels.administrator.finance-management.payment.index') }}" wire:navigate>{{ __('general.payments') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ __('general.payment_settings') }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>

            <flux:button
                class="shrink-0"
                variant="primary"
                color="zinc"
                icon="arrow-right"
                href="{{ route('panels.administrator.finance-management.payment.index') }}"
                wire:navigate
            >
                {{ __('general.payments') }}
            </flux:button>
        </div>

        <form wire:submit="save" class="space-y-6">
            <flux:card class="space-y-6">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-lg bg-teal-100 dark:bg-teal-500/20">
                        <flux:icon.credit-card class="size-5 text-teal-600 dark:text-teal-400" />
                    </div>
                    <div>
                        <flux:heading size="lg">{{ __('general.payment_settings') }}</flux:heading>
                        <flux:subheading>{{ __('general.payment_settings_hint') }}</flux:subheading>
                    </div>
                </div>

                <flux:select
                    wire:model="form.default"
                    variant="listbox"
                    searchable
                    label="{{ __('general.default_gateway') }}"
                    description="{{ __('general.default_gateway_hint') }}"
                >
                    @foreach ($driverOptions as $driver => $label)
                        <flux:select.option value="{{ $driver }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:checkbox.group
                    wire:model="form.enabled"
                    label="{{ __('general.enabled_gateways') }}"
                    description="{{ __('general.enabled_gateways_hint') }}"
                    class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
                >
                    @foreach ($driverOptions as $driver => $label)
                        <flux:checkbox value="{{ $driver }}" label="{{ $label }}" wire:key="enabled-gateway-{{ $driver }}" />
                    @endforeach
                </flux:checkbox.group>
            </flux:card>

            <flux:card class="space-y-6">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-lg bg-sky-100 dark:bg-sky-500/20">
                        <flux:icon.key class="size-5 text-sky-600 dark:text-sky-400" />
                    </div>
                    <div>
                        <flux:heading size="lg">{{ __('general.gateway_credentials') }}</flux:heading>
                        <flux:subheading>{{ __('general.gateway_credentials_hint') }}</flux:subheading>
                    </div>
                </div>

                <flux:select
                    wire:model.live="selectedDriver"
                    variant="listbox"
                    searchable
                    label="{{ __('general.gateway') }}"
                >
                    @foreach ($driverOptions as $driver => $label)
                        <flux:select.option value="{{ $driver }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>

                <div class="grid gap-4 md:grid-cols-2" wire:key="gateway-fields-{{ $selectedDriver }}">
                    @forelse ($editableKeys as $fieldKey)
                        @php
                            $fieldValue = $form->drivers[$selectedDriver][$fieldKey] ?? '';
                            $isBool = is_bool(config("payment.drivers.{$selectedDriver}.{$fieldKey}"));
                            $isSecret = PaymentGateways::isSecretKey($fieldKey);
                            $fieldLabel = str_replace('_', ' ', $fieldKey);
                        @endphp

                        @if ($isBool)
                            <flux:select
                                wire:model="form.drivers.{{ $selectedDriver }}.{{ $fieldKey }}"
                                variant="listbox"
                                label="{{ $fieldLabel }}"
                            >
                                <flux:select.option value="1">{{ __('general.yes') }}</flux:select.option>
                                <flux:select.option value="0">{{ __('general.no') }}</flux:select.option>
                            </flux:select>
                        @else
                            <flux:field>
                                <flux:label>{{ $fieldLabel }}</flux:label>
                                <div dir="ltr">
                                    <flux:input
                                        wire:model="form.drivers.{{ $selectedDriver }}.{{ $fieldKey }}"
                                        type="{{ $isSecret ? 'password' : 'text' }}"
                                        class="font-mono"
                                        clearable
                                    />
                                </div>
                                <flux:error name="form.drivers.{{ $selectedDriver }}.{{ $fieldKey }}" />
                            </flux:field>
                        @endif
                    @empty
                        <flux:text class="md:col-span-2">{{ __('general.no_editable_gateway_fields') }}</flux:text>
                    @endforelse
                </div>
            </flux:card>

            <flux:button type="submit" variant="primary" color="teal" icon="save" class="w-full">
                {{ __('general.save') }}
            </flux:button>
        </form>
    </div>
</div>
