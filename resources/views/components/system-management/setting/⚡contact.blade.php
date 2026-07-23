<?php

use App\Livewire\Forms\Settings\ContactSettingsForm;
use App\Settings\ContactSettings;
use Flux\Flux;
use Livewire\Component;

new class extends Component
{
    public ContactSettingsForm $form;

    public string $newPhoneNumber = '';

    public function mount(ContactSettings $settings): void
    {
        $this->form->setSettings($settings);
    }

    public function addPhoneNumber(): void
    {
        $this->resetErrorBag('newPhoneNumber');

        $value = trim($this->newPhoneNumber);

        if ($value === '') {
            return;
        }

        if (in_array($value, $this->form->phone_numbers, true)) {
            $this->addError('newPhoneNumber', __('general.duplicate_item'));

            return;
        }

        $this->form->phone_numbers[] = $value;
        $this->newPhoneNumber = '';
    }

    public function removePhoneNumber(int $index): void
    {
        unset($this->form->phone_numbers[$index]);

        $this->form->phone_numbers = array_values($this->form->phone_numbers);
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
            <div class="flex size-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-500/20">
                <flux:icon.phone class="size-5 text-blue-600 dark:text-blue-400" />
            </div>
            <div>
                <flux:heading size="lg">{{ __('general.contact_settings') }}</flux:heading>
                <flux:subheading>{{ __('general.contact_settings_hint') }}</flux:subheading>
            </div>
        </div>

        <form wire:submit="save" class="space-y-6">
            <flux:textarea wire:model="form.address" label="{{ __('general.address') }}" description="{{ __('general.address_hint') }}" rows="2" />

            <div class="grid gap-6 md:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('general.postal_code') }}</flux:label>
                    <flux:description>{{ __('general.postal_code_hint') }}</flux:description>
                    <div dir="ltr">
                        <flux:input wire:model="form.postal_code" icon="map-pin" placeholder="1234567890" class="font-mono" mask="9999999999" clearable />
                    </div>
                    <flux:error name="form.postal_code" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('general.fax') }}</flux:label>
                    <flux:description>{{ __('general.fax_hint') }}</flux:description>
                    <div dir="ltr">
                        <flux:input wire:model="form.fax" icon="printer" placeholder="021-12345678" class="font-mono" clearable />
                    </div>
                    <flux:error name="form.fax" />
                </flux:field>
            </div>

            <flux:input wire:model="form.support_email" type="email" icon="mail" label="{{ __('general.support_email') }}" description="{{ __('general.support_email_hint') }}" />

            {{-- Phone numbers (tag input, fully LTR with the add button at the end) --}}
            <flux:field>
                <flux:label>{{ __('general.phone_numbers') }}</flux:label>
                <flux:description>{{ __('general.phone_numbers_hint') }}</flux:description>
                <div dir="ltr">
                    <flux:input.group>
                        <flux:input wire:model="newPhoneNumber" icon="phone" placeholder="021-12345678" class="font-mono" wire:keydown.enter.prevent="addPhoneNumber" />
                        <flux:button type="button" icon="plus" wire:click="addPhoneNumber">{{ __('general.add') }}</flux:button>
                    </flux:input.group>
                </div>
                <flux:error name="newPhoneNumber" />

                <div class="mt-2 flex flex-wrap justify-start gap-2" dir="ltr">
                    @forelse ($form->phone_numbers as $index => $phone)
                        <flux:badge color="blue" wire:key="phone-number-{{ $index }}-{{ $phone }}">
                            <span class="font-mono">{{ $phone }}</span>
                            <flux:badge.close wire:click="removePhoneNumber({{ $index }})" />
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
