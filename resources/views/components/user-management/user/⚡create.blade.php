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

        Flux::toast(__('app.user_created'));
    }
};
?>

<flux:modal name="user-create-modal" flyout position="right" class="space-y-6">
    <div>
        <flux:heading size="lg">{{ __('app.create_user') }}</flux:heading>
    </div>

    <form wire:submit="save" class="space-y-6">
        <flux:input wire:model="form.first_name" label="{{ __('app.first_name') }}" />
        <flux:input wire:model="form.last_name" label="{{ __('app.last_name') }}" />
        <flux:input wire:model="form.mobile" label="{{ __('app.mobile') }}" />
        <flux:input wire:model="form.email" type="email" label="{{ __('app.email') }}" />
        <flux:input wire:model="form.username" label="{{ __('app.username') }}" />
        <flux:input wire:model="form.password" type="password" label="{{ __('app.password') }}" />

        <flux:button type="submit" variant="primary" color="orange" class="w-full">
            {{ __('app.save') }}
        </flux:button>
    </form>
</flux:modal>
