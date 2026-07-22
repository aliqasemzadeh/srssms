<?php

use App\Livewire\Forms\RoleForm;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public RoleForm $form;

    #[On('panels.administrator.user-management.role.create.assign-data')]
    public function assignData(): void
    {
        $this->form->reset();
        $this->resetValidation();

        Flux::modal('user-management.role.create')->show();
    }

    public function save(): void
    {
        $this->form->store();

        $this->form->reset();

        $this->dispatch('panels.administrator.user-management.role.index.refresh');

        Flux::modals()->close();

        Flux::toast(__('general.role_created'));
    }
};
?>

<flux:modal name="user-management.role.create" flyout position="right" class="space-y-6">
    <div>
        <flux:heading size="lg">{{ __('actions.create') }} {{ __('general.role') }}</flux:heading>
        <flux:subheading>{{ __('general.roles') }}</flux:subheading>
    </div>

    <form wire:submit="save" class="space-y-6">
        <flux:input wire:model="form.name" label="{{ __('general.name') }}" icon="shield" placeholder="{{ __('general.name') }}..." />

        <flux:button type="submit" variant="primary" color="teal" class="w-full">
            {{ __('actions.save') }}
        </flux:button>
    </form>
</flux:modal>
