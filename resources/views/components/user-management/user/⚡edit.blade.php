<?php

use App\Livewire\Forms\UserForm;
use App\Models\User;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public UserForm $form;

    #[On('panels.administrator.user-management.user.edit.assign-data')]
    public function assignData(User $user): void
    {
        $this->form->setModel($user);

        Flux::modal('user-edit-modal')->show();
    }

    public function save(): void
    {
        $this->form->update();

        $this->dispatch('panels.administrator.user-management.user.index.refresh');

        Flux::modals()->close();

        Flux::toast(__('app.user_updated'));
    }
};
?>

<flux:modal name="user-edit-modal" flyout position="right" class="space-y-6">
    <div>
        <flux:heading size="lg">{{ __('app.edit_user') }}</flux:heading>
    </div>

    <form wire:submit="save" class="space-y-6">
        <flux:input wire:model="form.first_name" label="{{ __('app.first_name') }}" />
        <flux:input wire:model="form.last_name" label="{{ __('app.last_name') }}" />
        <flux:input wire:model="form.mobile" label="{{ __('app.mobile') }}" />
        <flux:input wire:model="form.email" type="email" label="{{ __('app.email') }}" />
        <flux:input wire:model="form.username" label="{{ __('app.username') }}" />
        <flux:input wire:model="form.password" type="password" label="{{ __('app.password') }}" placeholder="{{ __('app.password_keep_blank') }}" />

        <flux:button type="submit" variant="primary" color="orange" class="w-full">
            {{ __('app.save') }}
        </flux:button>
    </form>
</flux:modal>
