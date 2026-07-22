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
            <flux:textarea wire:model="form.address" label="{{ __('general.address') }}" rows="2" />

            <flux:input wire:model="form.support_email" type="email" icon="mail" label="{{ __('general.support_email') }}" />

            {{-- Phone numbers (tag input) --}}
            <flux:field>
                <flux:label>{{ __('general.phone_numbers') }}</flux:label>
                <flux:input.group>
                    <div class="flex-1" dir="ltr">
                        <flux:input wire:model="newPhoneNumber" icon="phone" placeholder="021-12345678" class="font-mono" wire:keydown.enter.prevent="addPhoneNumber" />
                    </div>
                    <flux:button type="button" icon="plus" wire:click="addPhoneNumber">{{ __('general.add') }}</flux:button>
                </flux:input.group>
                <flux:error name="newPhoneNumber" />

                <div class="mt-2 flex flex-wrap gap-2">
                    @forelse ($form->phone_numbers as $index => $phone)
                        <flux:badge color="blue" wire:key="phone-number-{{ $index }}-{{ $phone }}">
                            <span dir="ltr" class="font-mono">{{ $phone }}</span>
                            <flux:badge.close wire:click="removePhoneNumber({{ $index }})" />
                        </flux:badge>
                    @empty
                        <flux:text size="sm">{{ __('general.no_items_added') }}</flux:text>
                    @endforelse
                </div>
            </flux:field>

            <flux:button type="submit" variant="primary" color="teal" icon="save" class="w-full">
                {{ __('general.save') }}
            </flux:button>
        </form>
    </flux:card>
</div>
