<?php

use App\Livewire\Forms\Settings\MaintenanceSettingsForm;
use App\Settings\MaintenanceSettings;
use Flux\Flux;
use Illuminate\Support\Facades\Artisan;
use Livewire\Component;

new class extends Component
{
    public MaintenanceSettingsForm $form;

    public function mount(MaintenanceSettings $settings): void
    {
        $this->form->setSettings($settings);
    }

    public function save(): void
    {
        $settings = $this->form->store();

        if ($settings->is_maintenance_mode) {
            // Re-apply so a changed secret token always takes effect.
            if (app()->isDownForMaintenance()) {
                Artisan::call('up');
            }

            Artisan::call('down', array_filter([
                '--secret' => $settings->secret_token,
            ]));
        } elseif (app()->isDownForMaintenance()) {
            Artisan::call('up');
        }

        Flux::toast(__('general.settings_saved'));
    }
};
?>

<div>
    <flux:card class="space-y-6">
        <div class="flex items-center gap-3">
            <div class="flex size-10 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-500/20">
                <flux:icon.construction class="size-5 text-amber-600 dark:text-amber-400" />
            </div>
            <div>
                <flux:heading size="lg">{{ __('general.maintenance_settings') }}</flux:heading>
                <flux:subheading>{{ __('general.maintenance_settings_hint') }}</flux:subheading>
            </div>
        </div>

        <form wire:submit="save" class="space-y-6">
            <flux:callout icon="triangle-alert" color="amber" inline>
                <flux:callout.text>{{ __('general.maintenance_warning') }}</flux:callout.text>
            </flux:callout>

            <flux:field variant="inline">
                <flux:label>{{ __('general.maintenance_mode') }}</flux:label>
                <flux:switch wire:model.live="form.is_maintenance_mode" />
                <flux:error name="form.is_maintenance_mode" />
            </flux:field>
            <flux:text size="sm">{{ __('general.maintenance_mode_hint') }}</flux:text>

            <flux:input wire:model="form.secret_token" type="password" viewable label="{{ __('general.secret_token') }}" description="{{ __('general.secret_token_hint') }}" />

            <flux:textarea wire:model="form.message" label="{{ __('general.maintenance_message') }}" rows="3" />

            <flux:button type="submit" variant="primary" color="teal" icon="save" class="w-full">
                {{ __('general.save') }}
            </flux:button>
        </form>
    </flux:card>
</div>
