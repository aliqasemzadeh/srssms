<?php

use Livewire\Attributes\Url;
use Livewire\Component;

new class extends Component
{
    #[Url]
    public string $tab = 'general';
};
?>

<div>
    <x-slot name="title">{{ __('general.settings') }} - {{ config('app.name') }}</x-slot>

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('panels.administrator.dashboard.index') }}" icon="home" />
                <flux:breadcrumbs.item>{{ __('general.system_management') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ __('general.settings') }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
        </div>

        <flux:tab.group>
            <flux:tabs wire:model.live="tab" variant="segmented" scrollable>
                <flux:tab name="general" icon="settings">{{ __('general.general_settings') }}</flux:tab>
                <flux:tab name="maintenance" icon="construction">{{ __('general.maintenance_settings') }}</flux:tab>
                <flux:tab name="security" icon="shield">{{ __('general.security_settings') }}</flux:tab>
                <flux:tab name="contact" icon="phone">{{ __('general.contact_settings') }}</flux:tab>
                <flux:tab name="social" icon="share-2">{{ __('general.social_settings') }}</flux:tab>
            </flux:tabs>

            <flux:tab.panel name="general">
                <livewire:system-management.setting.general :key="'setting-general'" />
            </flux:tab.panel>

            <flux:tab.panel name="maintenance">
                <livewire:system-management.setting.maintenance :key="'setting-maintenance'" />
            </flux:tab.panel>

            <flux:tab.panel name="security">
                <livewire:system-management.setting.security :key="'setting-security'" />
            </flux:tab.panel>

            <flux:tab.panel name="contact">
                <livewire:system-management.setting.contact :key="'setting-contact'" />
            </flux:tab.panel>

            <flux:tab.panel name="social">
                <livewire:system-management.setting.social :key="'setting-social'" />
            </flux:tab.panel>
        </flux:tab.group>
    </div>
</div>
