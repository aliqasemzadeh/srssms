<?php

use App\Livewire\Forms\Settings\GeneralSettingsForm;
use App\Settings\GeneralSettings;
use Flux\Flux;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public GeneralSettingsForm $form;

    public function mount(GeneralSettings $settings): void
    {
        $this->form->setSettings($settings);
    }

    public function save(): void
    {
        $this->form->store();

        Flux::toast(__('general.settings_saved'));
    }

    public function removeLogo(): void
    {
        $this->form->removeLogo();

        Flux::toast(__('general.settings_saved'));
    }

    public function removeFavicon(): void
    {
        $this->form->removeFavicon();

        Flux::toast(__('general.settings_saved'));
    }
};
?>

<div>
    <flux:card class="space-y-6">
        <div class="flex items-center gap-3">
            <div class="flex size-10 items-center justify-center rounded-lg bg-teal-100 dark:bg-teal-500/20">
                <flux:icon.settings class="size-5 text-teal-600 dark:text-teal-400" />
            </div>
            <div>
                <flux:heading size="lg">{{ __('general.general_settings') }}</flux:heading>
                <flux:subheading>{{ __('general.general_settings_hint') }}</flux:subheading>
            </div>
        </div>

        <form wire:submit="save" class="space-y-6">
            <div class="grid gap-6 md:grid-cols-2">
                <flux:input wire:model="form.site_name" label="{{ __('general.site_name') }}" />
                <flux:input wire:model="form.site_short_name" label="{{ __('general.site_short_name') }}" description="{{ __('general.site_short_name_hint') }}" />
            </div>

            <flux:textarea wire:model="form.site_description" label="{{ __('general.site_description') }}" description="{{ __('general.site_description_hint') }}" rows="3" />

            <div class="grid gap-6 md:grid-cols-2">
                {{-- Logo --}}
                <flux:field>
                    <flux:label>{{ __('general.site_logo') }}</flux:label>

                    @if ($form->current_logo)
                        <div class="flex items-center justify-between gap-3 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                            <img src="{{ asset('storage/' . $form->current_logo) }}" alt="{{ __('general.site_logo') }}" class="h-12 w-auto rounded-md object-contain" />
                            <flux:tooltip content="{{ __('general.remove') }}">
                                <flux:button type="button" size="xs" variant="danger" icon="trash" icon:variant="outline" wire:click="removeLogo" wire:confirm="{{ __('general.are_you_sure') }}" />
                            </flux:tooltip>
                        </div>
                    @endif

                    <flux:file-upload wire:model="form.site_logo">
                        <flux:file-upload.dropzone inline heading="{{ __('general.upload_file_hint') }}" text="PNG, JPG, SVG (max 2MB)" />
                    </flux:file-upload>
                    <flux:error name="form.site_logo" />
                </flux:field>

                {{-- Favicon --}}
                <flux:field>
                    <flux:label>{{ __('general.site_favicon') }}</flux:label>

                    @if ($form->current_favicon)
                        <div class="flex items-center justify-between gap-3 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                            <img src="{{ asset('storage/' . $form->current_favicon) }}" alt="{{ __('general.site_favicon') }}" class="size-8 rounded-md object-contain" />
                            <flux:tooltip content="{{ __('general.remove') }}">
                                <flux:button type="button" size="xs" variant="danger" icon="trash" icon:variant="outline" wire:click="removeFavicon" wire:confirm="{{ __('general.are_you_sure') }}" />
                            </flux:tooltip>
                        </div>
                    @endif

                    <flux:file-upload wire:model="form.site_favicon">
                        <flux:file-upload.dropzone inline heading="{{ __('general.upload_file_hint') }}" text="PNG, ICO, SVG (max 1MB)" />
                    </flux:file-upload>
                    <flux:error name="form.site_favicon" />
                </flux:field>
            </div>

            <flux:button type="submit" variant="primary" color="teal" icon="save" class="w-full">
                {{ __('general.save') }}
            </flux:button>
        </form>
    </flux:card>
</div>
