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

        Flux::toast(__('general.user_updated'));
    }
};
?>

<flux:modal name="user-edit-modal" flyout position="right" class="space-y-6">
    <div>
        <flux:heading size="lg">{{ __('general.edit_user') }}</flux:heading>
    </div>

    <form wire:submit="save" class="space-y-6">
        <flux:input wire:model="form.first_name" label="{{ __('general.first_name') }}" />
        <flux:input wire:model="form.last_name" label="{{ __('general.last_name') }}" />
        <flux:input wire:model="form.mobile" label="{{ __('general.mobile') }}" />
        <flux:input wire:model="form.email" type="email" label="{{ __('general.email') }}" />
        <flux:input wire:model="form.username" label="{{ __('general.username') }}" />
        <flux:input wire:model="form.password" type="password" label="{{ __('general.password') }}" placeholder="Leave blank to keep current password" />

        <div class="flex">
            <flux:spacer />
            <flux:button type="submit" variant="primary" color="teal" class="w-full">
                {{ __('general.save') }}
            </flux:button>
        </div>
    </form>
</flux:modal>
