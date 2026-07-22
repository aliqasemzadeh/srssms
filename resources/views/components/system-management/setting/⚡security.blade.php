<?php

use App\Livewire\Forms\Settings\SecuritySettingsForm;
use App\Settings\SecuritySettings;
use Flux\Flux;
use Livewire\Component;

new class extends Component
{
    public SecuritySettingsForm $form;

    public string $newBannedUsername = '';

    public string $newBannedIp = '';

    public function mount(SecuritySettings $settings): void
    {
        $this->form->setSettings($settings);
    }

    public function addBannedUsername(): void
    {
        $this->resetErrorBag('newBannedUsername');

        $value = trim($this->newBannedUsername);

        if ($value === '') {
            return;
        }

        if (in_array($value, $this->form->banned_usernames, true)) {
            $this->addError('newBannedUsername', __('general.duplicate_item'));

            return;
        }

        $this->form->banned_usernames[] = $value;
        $this->newBannedUsername = '';
    }

    public function removeBannedUsername(int $index): void
    {
        unset($this->form->banned_usernames[$index]);

        $this->form->banned_usernames = array_values($this->form->banned_usernames);
    }

    public function addBannedIp(): void
    {
        $this->resetErrorBag('newBannedIp');

        $value = trim($this->newBannedIp);

        if ($value === '') {
            return;
        }

        if (filter_var($value, FILTER_VALIDATE_IP) === false) {
            $this->addError('newBannedIp', __('general.invalid_ip'));

            return;
        }

        if (in_array($value, $this->form->banned_ips, true)) {
            $this->addError('newBannedIp', __('general.duplicate_item'));

            return;
        }

        $this->form->banned_ips[] = $value;
        $this->newBannedIp = '';
    }

    public function removeBannedIp(int $index): void
    {
        unset($this->form->banned_ips[$index]);

        $this->form->banned_ips = array_values($this->form->banned_ips);
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
            <div class="flex size-10 items-center justify-center rounded-lg bg-red-100 dark:bg-red-500/20">
                <flux:icon.shield class="size-5 text-red-600 dark:text-red-400" />
            </div>
            <div>
                <flux:heading size="lg">{{ __('general.security_settings') }}</flux:heading>
                <flux:subheading>{{ __('general.security_settings_hint') }}</flux:subheading>
            </div>
        </div>

        <form wire:submit="save" class="space-y-6">
            <flux:field variant="inline">
                <flux:label>{{ __('general.registration_enabled') }}</flux:label>
                <flux:switch wire:model.live="form.is_registration_enabled" />
                <flux:error name="form.is_registration_enabled" />
            </flux:field>
            <flux:text size="sm">{{ __('general.registration_enabled_hint') }}</flux:text>

            <flux:separator variant="subtle" />

            {{-- Banned usernames (tag input) --}}
            <flux:field>
                <flux:label>{{ __('general.banned_usernames') }}</flux:label>
                <flux:description>{{ __('general.banned_usernames_hint') }}</flux:description>
                <flux:input.group>
                    <flux:input wire:model="newBannedUsername" icon="ban" placeholder="{{ __('general.type_and_press_enter') }}" wire:keydown.enter.prevent="addBannedUsername" />
                    <flux:button type="button" icon="plus" wire:click="addBannedUsername">{{ __('general.add') }}</flux:button>
                </flux:input.group>
                <flux:error name="newBannedUsername" />

                <div class="mt-2 flex flex-wrap gap-2">
                    @forelse ($form->banned_usernames as $index => $username)
                        <flux:badge color="red" wire:key="banned-username-{{ $index }}-{{ $username }}">
                            {{ $username }}
                            <flux:badge.close wire:click="removeBannedUsername({{ $index }})" />
                        </flux:badge>
                    @empty
                        <flux:text size="sm">{{ __('general.no_items_added') }}</flux:text>
                    @endforelse
                </div>
            </flux:field>

            {{-- Banned IPs (tag input, fully LTR with the add button at the end) --}}
            <flux:field>
                <flux:label>{{ __('general.banned_ips') }}</flux:label>
                <flux:description>{{ __('general.banned_ips_hint') }}</flux:description>
                <div dir="ltr">
                    <flux:input.group>
                        <flux:input wire:model="newBannedIp" icon="ban" placeholder="192.168.1.1" class="font-mono" wire:keydown.enter.prevent="addBannedIp" />
                        <flux:button type="button" icon="plus" wire:click="addBannedIp">{{ __('general.add') }}</flux:button>
                    </flux:input.group>
                </div>
                <flux:error name="newBannedIp" />

                <div class="mt-2 flex flex-wrap justify-start gap-2" dir="ltr">
                    @forelse ($form->banned_ips as $index => $ip)
                        <flux:badge color="rose" wire:key="banned-ip-{{ $index }}-{{ $ip }}">
                            <span class="font-mono">{{ $ip }}</span>
                            <flux:badge.close wire:click="removeBannedIp({{ $index }})" />
                        </flux:badge>
                    @empty
                        <flux:text size="sm" dir="rtl">{{ __('general.no_items_added') }}</flux:text>
                    @endforelse
                </div>
            </flux:field>

            <flux:button type="submit" variant="primary" color="teal" icon="save" class="w-full">
                {{ __('general.save') }}
            </flux:button>
        </form>
    </flux:card>
</div>
