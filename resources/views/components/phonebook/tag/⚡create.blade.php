<?php

use App\Livewire\Forms\Phonebook\TagForm;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public TagForm $form;

    #[On('panels.user.phonebook.tag.create.assign-data')]
    public function assignData(): void
    {
        $this->form->reset();
        $this->resetValidation();
        Flux::modal('phonebook.tag.create')->show();
    }

    public function save(): void
    {
        $this->form->store();
        $this->form->reset();
        $this->dispatch('panels.user.phonebook.tag.index.refresh');
        Flux::modals()->close();
        Flux::toast(__('general.phonebook_tag_created'));
    }
};
?>

<flux:modal name="phonebook.tag.create" flyout position="right" class="space-y-6">
    <div>
        <flux:heading size="lg">{{ __('actions.create') }} {{ __('general.phonebook_tag') }}</flux:heading>
        <flux:subheading>{{ __('general.phonebook_tags') }}</flux:subheading>
    </div>

    <form wire:submit="save" class="space-y-6">
        <flux:input wire:model="form.name" label="{{ __('general.name') }}" icon="tags" />
        <flux:button type="submit" variant="primary" color="teal" class="w-full">{{ __('actions.save') }}</flux:button>
    </form>
</flux:modal>
