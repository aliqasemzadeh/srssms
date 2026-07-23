<?php

use Livewire\Attributes\Url;
use Livewire\Component;

new class extends Component
{
    #[Url]
    public string $tab = 'password';
};
?>

<div>
    <x-slot name="title">{{ __('general.settings') }} - {{ config('app.name') }}</x-slot>

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('panels.user.dashboard.index') }}" icon="home" />
                <flux:breadcrumbs.item>{{ __('general.settings') }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
        </div>

        <flux:tab.group>
            <flux:tabs wire:model.live="tab" variant="segmented" scrollable>
                <flux:tab name="password" icon="lock">{{ __('general.change_password') }}</flux:tab>
            </flux:tabs>

            <flux:tab.panel name="password">
                <livewire:pages::panels.user.setting.password.index :key="'user-setting-password'" />
            </flux:tab.panel>
        </flux:tab.group>
    </div>
</div>
