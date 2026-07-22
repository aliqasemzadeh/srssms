<?php

use App\Livewire\Forms\Settings\SocialSettingsForm;
use App\Settings\SocialSettings;
use Flux\Flux;
use Livewire\Component;

new class extends Component
{
    public SocialSettingsForm $form;

    public function mount(SocialSettings $settings): void
    {
        $this->form->setSettings($settings);
    }

    public function save(): void
    {
        $this->form->store();

        Flux::toast(__('general.settings_saved'));
    }
};
?>

<div>
    <flux:card class="space-y-6">
        <div class="flex items-center gap-3">
            <div class="flex size-10 items-center justify-center rounded-lg bg-violet-100 dark:bg-violet-500/20">
                <flux:icon.share-2 class="size-5 text-violet-600 dark:text-violet-400" />
            </div>
            <div>
                <flux:heading size="lg">{{ __('general.social_settings') }}</flux:heading>
                <flux:subheading>{{ __('general.social_settings_hint') }}</flux:subheading>
            </div>
        </div>

        <form wire:submit="save" class="space-y-6">
            <div class="grid gap-6 md:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('general.telegram') }}</flux:label>
                    <div dir="ltr">
                        <flux:input wire:model="form.telegram" icon="send" placeholder="https://t.me/username" clearable />
                    </div>
                    <flux:error name="form.telegram" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('general.instagram') }}</flux:label>
                    <div dir="ltr">
                        <flux:input wire:model="form.instagram" icon="camera" placeholder="https://instagram.com/username" clearable />
                    </div>
                    <flux:error name="form.instagram" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('general.linkedin') }}</flux:label>
                    <div dir="ltr">
                        <flux:input wire:model="form.linkedin" icon="briefcase" placeholder="https://linkedin.com/company/name" clearable />
                    </div>
                    <flux:error name="form.linkedin" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('general.x_twitter') }}</flux:label>
                    <div dir="ltr">
                        <flux:input wire:model="form.x_twitter" icon="at-sign" placeholder="https://x.com/username" clearable />
                    </div>
                    <flux:error name="form.x_twitter" />
                </flux:field>
            </div>

            <flux:button type="submit" variant="primary" color="teal" icon="save" class="w-full">
                {{ __('general.save') }}
            </flux:button>
        </form>
    </flux:card>
</div>
