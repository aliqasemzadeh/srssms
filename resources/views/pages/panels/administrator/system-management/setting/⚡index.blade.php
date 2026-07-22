<?php

use Livewire\Component;

new class extends Component
{
    //
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

        <flux:card>
            <div class="flex flex-col items-center justify-center gap-3 py-12 text-zinc-400">
                <flux:icon.settings class="size-10" />
                <flux:heading size="lg">{{ __('general.settings') }}</flux:heading>
                <flux:subheading>{{ __('general.coming_soon') }}</flux:subheading>
            </div>
        </flux:card>
    </div>
</div>
