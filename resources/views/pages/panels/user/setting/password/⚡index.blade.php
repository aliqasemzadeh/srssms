<?php

use App\Livewire\Forms\ChangePasswordForm;
use Flux\Flux;
use Livewire\Component;

new class extends Component
{
    public ChangePasswordForm $form;

    public function save(): void
    {
        $this->form->update();

        Flux::toast(__('general.password_changed'));
    }
};
?>

<div>
    <flux:card class="space-y-6">
        <div class="flex items-center gap-3">
            <div class="flex size-10 items-center justify-center rounded-lg bg-orange-100 dark:bg-orange-500/20">
                <flux:icon.lock class="size-5 text-orange-600 dark:text-orange-400" />
            </div>
            <div>
                <flux:heading size="lg">{{ __('general.change_password') }}</flux:heading>
                <flux:subheading>{{ __('general.change_password_hint') }}</flux:subheading>
            </div>
        </div>

        <form wire:submit="save" class="space-y-6">
            <flux:input
                wire:model="form.current_password"
                type="password"
                label="{{ __('general.current_password') }}"
                icon="lock"
                placeholder="••••••••"
                viewable
            />

            <flux:input
                wire:model="form.password"
                type="password"
                label="{{ __('general.new_password') }}"
                icon="key"
                placeholder="••••••••"
                viewable
            />

            <flux:input
                wire:model="form.password_confirmation"
                type="password"
                label="{{ __('general.password_confirmation') }}"
                icon="key"
                placeholder="••••••••"
                viewable
            />

            <flux:button type="submit" variant="primary" color="orange" icon="save" class="w-full">
                {{ __('general.save') }}
            </flux:button>
        </form>
    </flux:card>
</div>
