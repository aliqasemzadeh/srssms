<?php

use App\Livewire\Forms\RoleForm;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;
use Spatie\Permission\Models\Role;

new class extends Component
{
    public RoleForm $form;

    #[On('panels.administrator.user-management.role.edit.assign-data')]
    public function assignData(int $role): void
    {
        $this->form->setModel(Role::findById($role));
        $this->resetValidation();

        Flux::modal('user-management.role.edit')->show();
    }

    public function save(): void
    {
        $this->form->update();

        $this->dispatch('panels.administrator.user-management.role.index.refresh');

        Flux::modals()->close();

        Flux::toast(__('general.role_updated'));
    }
};
?>

<flux:modal name="user-management.role.edit" flyout position="right" class="space-y-6">
    <div>
        <flux:heading size="lg">{{ __('actions.edit') }} {{ __('general.role') }}</flux:heading>
        <flux:subheading>{{ __('general.roles') }}</flux:subheading>
    </div>

    <form wire:submit="save" class="space-y-6">
        <flux:input wire:model="form.name" label="{{ __('general.name') }}" icon="shield" placeholder="{{ __('general.name') }}..." />

        <flux:button type="submit" variant="primary" color="teal" class="w-full">
            {{ __('actions.save') }}
        </flux:button>
    </form>
</flux:modal>
