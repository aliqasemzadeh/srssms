<?php

use App\Livewire\Forms\Phonebook\GroupForm;
use App\Models\Phonebook\Group;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public GroupForm $form;

    #[On('panels.user.phonebook.group.edit.assign-data')]
    public function assignData(int $group): void
    {
        $model = Group::query()->where('user_id', Auth::id())->findOrFail($group);
        $this->form->setModel($model);
        $this->resetValidation();

        Flux::modal('phonebook.group.edit')->show();
    }

    public function save(): void
    {
        $this->form->update();
        $this->dispatch('panels.user.phonebook.group.index.refresh');
        Flux::modals()->close();
        Flux::toast(__('general.phonebook_group_updated'));
    }
};
?>

<flux:modal name="phonebook.group.edit" flyout position="right" class="space-y-6">
    <div>
        <flux:heading size="lg">{{ __('actions.edit') }} {{ __('general.phonebook_group') }}</flux:heading>
        <flux:subheading>{{ __('general.phonebook_groups') }}</flux:subheading>
    </div>

    <form wire:submit="save" class="space-y-6">
        <flux:input wire:model="form.name" label="{{ __('general.name') }}" icon="users" />
        <flux:textarea wire:model="form.description" label="{{ __('general.description') }}" rows="3" />
        <flux:button type="submit" variant="primary" color="orange" class="w-full">{{ __('actions.save') }}</flux:button>
    </form>
</flux:modal>
