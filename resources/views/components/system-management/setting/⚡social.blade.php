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
            @php
                $networks = [
                    'telegram' => ['icon' => 'send', 'placeholder' => 'https://t.me/username'],
                    'eitaa' => ['icon' => 'message-circle', 'placeholder' => 'https://eitaa.com/username'],
                    'bale' => ['icon' => 'message-square', 'placeholder' => 'https://ble.ir/username'],
                    'rubika' => ['icon' => 'smartphone', 'placeholder' => 'https://rubika.ir/username'],
                    'soroush' => ['icon' => 'messages-square', 'placeholder' => 'https://splus.ir/username'],
                    'aparat' => ['icon' => 'video', 'placeholder' => 'https://aparat.com/username'],
                    'instagram' => ['icon' => 'camera', 'placeholder' => 'https://instagram.com/username'],
                    'linkedin' => ['icon' => 'briefcase', 'placeholder' => 'https://linkedin.com/company/name'],
                    'x_twitter' => ['icon' => 'at-sign', 'placeholder' => 'https://x.com/username'],
                ];
            @endphp

            <div class="grid gap-6 md:grid-cols-2">
                @foreach ($networks as $network => $meta)
                    <flux:field>
                        <flux:label>{{ __('general.' . $network) }}</flux:label>
                        <flux:description>{{ __('general.social_link_hint', ['network' => __('general.' . $network)]) }}</flux:description>
                        <div dir="ltr">
                            <flux:input wire:model="form.{{ $network }}" icon="{{ $meta['icon'] }}" placeholder="{{ $meta['placeholder'] }}" clearable />
                        </div>
                        <flux:error name="form.{{ $network }}" />
                    </flux:field>
                @endforeach
            </div>

            <flux:button type="submit" variant="primary" color="teal" icon="save" class="w-full">
                {{ __('general.save') }}
            </flux:button>
        </form>
    </flux:card>
</div>
