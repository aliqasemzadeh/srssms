<?php

use App\Livewire\Forms\Settings\SmsSettingsForm;
use App\Models\Sms\Gateway;
use App\Services\Sms\SmsManager;
use App\Settings\SmsSettings;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public SmsSettingsForm $form;

    public function mount(SmsSettings $settings): void
    {
        $this->form->setSettings($settings);
    }

    #[Computed]
    public function gateways(): Collection
    {
        return Gateway::query()
            ->with('provider')
            ->where('is_active', true)
            ->orderBy('title')
            ->get();
    }

    public function save(): void
    {
        $this->form->store();
        Flux::toast(__('general.settings_saved'));
    }
};
?>

@php
    $driverOptions = app(SmsManager::class)->driverOptions();
@endphp

<div>
    <x-slot name="title">{{ __('general.sms_settings') }} - {{ config('app.name') }}</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('panels.administrator.dashboard.index') }}" icon="home" wire:navigate />
                <flux:breadcrumbs.item>{{ __('general.sms_management') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ __('general.sms_settings') }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
        </div>

        <form wire:submit="save" class="space-y-6">
            <flux:card class="space-y-6">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-lg bg-teal-100 dark:bg-teal-500/20">
                        <flux:icon.message-square-text class="size-5 text-teal-600 dark:text-teal-400" />
                    </div>
                    <div>
                        <flux:heading size="lg">{{ __('general.sms_settings') }}</flux:heading>
                        <flux:subheading>{{ __('general.sms_settings_hint') }}</flux:subheading>
                    </div>
                </div>

                <flux:select wire:model="form.default_driver" variant="listbox" searchable label="{{ __('general.default_sms_driver') }}">
                    @foreach ($driverOptions as $value => $label)
                        <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model="form.default_gateway_id" variant="listbox" searchable label="{{ __('general.default_sms_gateway') }}" clearable>
                    @foreach ($this->gateways as $gateway)
                        <flux:select.option value="{{ $gateway->id }}">
                            {{ $gateway->title }} — {{ $gateway->provider?->name }} (<span dir="ltr">{{ $gateway->number }}</span>)
                        </flux:select.option>
                    @endforeach
                </flux:select>

                <flux:button type="submit" variant="primary" color="teal" class="w-full sm:w-auto">
                    {{ __('actions.save') }}
                </flux:button>
            </flux:card>
        </form>
    </div>
</div>
