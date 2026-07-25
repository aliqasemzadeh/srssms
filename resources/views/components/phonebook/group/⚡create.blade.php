<?php

use App\Livewire\Forms\Phonebook\GroupForm;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public GroupForm $form;

    #[On('panels.user.phonebook.group.create.assign-data')]
    public function assignData(): void
    {
        $this->form->reset();
        $this->resetValidation();

        Flux::modal('phonebook.group.create')->show();
    }

    public function save(): void
    {
        $this->form->store();
        $this->form->reset();
        $this->dispatch('panels.user.phonebook.group.index.refresh');
        Flux::modals()->close();
        Flux::toast(__('general.phonebook_group_created'));
    }
};
?>

<flux:modal name="phonebook.group.create" flyout position="right" class="space-y-6">
    <div>
        <flux:heading size="lg">{{ __('actions.create') }} {{ __('general.phonebook_group') }}</flux:heading>
        <flux:subheading>{{ __('general.phonebook_groups') }}</flux:subheading>
    </div>

    <form wire:submit="save" class="space-y-6">
        <flux:input wire:model="form.name" label="{{ __('general.name') }}" icon="users" />
        <flux:textarea wire:model="form.description" label="{{ __('general.description') }}" rows="3" />
        <flux:button type="submit" variant="primary" color="teal" class="w-full">{{ __('actions.save') }}</flux:button>
    </form>
</flux:modal>
