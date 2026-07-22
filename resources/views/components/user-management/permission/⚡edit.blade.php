<?php

use App\Livewire\Forms\PermissionForm;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;
use Spatie\Permission\Models\Permission;

new class extends Component
{
    public PermissionForm $form;

    #[On('panels.administrator.user-management.permission.edit.assign-data')]
    public function assignData(int $permission): void
    {
        $this->form->setModel(Permission::findById($permission));
        $this->resetValidation();

        Flux::modal('user-management.permission.edit')->show();
    }

    public function save(): void
    {
        $this->form->update();

        $this->dispatch('panels.administrator.user-management.permission.index.refresh');

        Flux::modals()->close();

        Flux::toast(__('general.permission_updated'));
    }
};
?>

<flux:modal name="user-management.permission.edit" flyout position="right" class="space-y-6 md:w-[28rem]">
    <div>
        <flux:heading size="lg">{{ __('actions.edit') }} {{ __('general.permission') }}</flux:heading>
        <flux:subheading>{{ __('general.permissions') }}</flux:subheading>
    </div>

    <form wire:submit="save" class="space-y-6">
        <flux:input
            wire:model="form.name"
            label="{{ __('general.permission_name') }}"
            placeholder="{{ __('general.permission_name_placeholder') }}"
            icon="key"
            dir="ltr"
            clearable
        />

        <flux:button type="submit" variant="primary" color="teal" class="w-full">
            {{ __('actions.save') }}
        </flux:button>
    </form>
</flux:modal>
