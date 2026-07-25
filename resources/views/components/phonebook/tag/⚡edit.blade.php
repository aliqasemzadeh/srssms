<?php

use App\Livewire\Forms\Phonebook\TagForm;
use App\Models\Phonebook\Contact;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;
use Spatie\Tags\Tag;

new class extends Component
{
    public TagForm $form;

    #[On('panels.user.phonebook.tag.edit.assign-data')]
    public function assignData(int $tag): void
    {
        $model = Tag::query()
            ->where('type', Contact::tagTypeFor(Auth::user()))
            ->findOrFail($tag);

        $this->form->setModel($model);
        $this->resetValidation();
        Flux::modal('phonebook.tag.edit')->show();
    }

    public function save(): void
    {
        $this->form->update();
        $this->dispatch('panels.user.phonebook.tag.index.refresh');
        Flux::modals()->close();
        Flux::toast(__('general.phonebook_tag_updated'));
    }
};
?>

<flux:modal name="phonebook.tag.edit" flyout position="right" class="space-y-6">
    <div>
        <flux:heading size="lg">{{ __('actions.edit') }} {{ __('general.phonebook_tag') }}</flux:heading>
        <flux:subheading>{{ __('general.phonebook_tags') }}</flux:subheading>
    </div>

    <form wire:submit="save" class="space-y-6">
        <flux:input wire:model="form.name" label="{{ __('general.name') }}" icon="tags" />
        <flux:button type="submit" variant="primary" color="orange" class="w-full">{{ __('actions.save') }}</flux:button>
    </form>
</flux:modal>
