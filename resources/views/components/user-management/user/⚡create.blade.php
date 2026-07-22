<?php

use App\Livewire\Forms\UserForm;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public UserForm $form;

    #[On('panels.administrator.user-management.user.create.assign-data')]
    public function assignData(): void
    {
        $this->form->reset();

        Flux::modal('user-create-modal')->show();
    }

    public function save(): void
    {
        $this->form->store();

        $this->form->reset();

        $this->dispatch('panels.administrator.user-management.user.index.refresh');

        Flux::modals()->close();

        Flux::toast(__('general.user_created'));
    }
};
?>

<flux:modal name="user-create-modal" flyout position="right" class="space-y-6">
    <div>
        <flux:heading size="lg">{{ __('actions.create') }} {{ __('general.user') }}</flux:heading>
    </div>

    <form wire:submit="save" class="space-y-6">
        <flux:input wire:model="form.first_name" label="{{ __('general.first_name') }}" />
        <flux:input wire:model="form.last_name" label="{{ __('general.last_name') }}" />
        <flux:input wire:model="form.mobile" label="{{ __('general.mobile') }}" />
        <flux:input wire:model="form.email" type="email" label="{{ __('general.email') }}" />
        <flux:input wire:model="form.username" label="{{ __('general.username') }}" />
        <flux:input wire:model="form.password" type="password" label="{{ __('general.password') }}" viewable />

        <flux:button type="submit" variant="primary" color="teal" class="w-full">
            {{ __('actions.save') }}
        </flux:button>
    </form>
</flux:modal>
