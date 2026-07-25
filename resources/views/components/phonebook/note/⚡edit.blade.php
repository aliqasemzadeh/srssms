<?php

use App\Enums\Phonebook\ContactNoteStatusEnum;
use App\Livewire\Forms\Phonebook\NoteForm;
use App\Models\Phonebook\Contact;
use App\Models\Phonebook\Note;
use Flux\Flux;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public NoteForm $form;

    #[On('panels.user.phonebook.note.edit.assign-data')]
    public function assignData(int $note): void
    {
        $model = Note::query()->ownedBy(Auth::user())->findOrFail($note);
        $this->form->setModel($model);
        $this->resetValidation();
        unset($this->contacts);

        Flux::modal('phonebook.note.edit')->show();
    }

    #[Computed]
    public function contacts(): Collection
    {
        return Contact::query()
            ->ownedBy(Auth::user())
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'mobile']);
    }

    public function save(): void
    {
        $this->form->update();
        $this->dispatch('panels.user.phonebook.note.index.refresh');
        $this->dispatch('panels.user.phonebook.view.refresh');
        Flux::modals()->close();
        Flux::toast(__('general.note_updated'));
    }
};
?>

<flux:modal name="phonebook.note.edit" flyout position="right" class="space-y-6">
    <div>
        <flux:heading size="lg">{{ __('actions.edit') }} {{ __('general.note') }}</flux:heading>
        <flux:subheading>{{ __('general.notes') }}</flux:subheading>
    </div>

    <form wire:submit="save" class="space-y-6">
        <flux:select wire:model="form.contact_id" variant="listbox" searchable label="{{ __('general.contact') }}">
            @foreach ($this->contacts as $contact)
                <flux:select.option value="{{ $contact->id }}">{{ $contact->full_name }} — {{ $contact->mobile }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:textarea wire:model="form.body" label="{{ __('general.note') }}" rows="4" />

        <flux:select wire:model="form.status" variant="listbox" searchable label="{{ __('general.note_status') }}" clearable>
            @foreach (ContactNoteStatusEnum::options() as $value => $label)
                <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>

        @if (app()->getLocale() === 'fa')
            <x-persian-date-picker wire:model="form.remind_at" label="{{ __('general.remind_at') }}" description="{{ __('general.remind_at_hint') }}" />
        @else
            <flux:input wire:model="form.remind_at" type="date" label="{{ __('general.remind_at') }}" description="{{ __('general.remind_at_hint') }}" />
        @endif

        <flux:button type="submit" variant="primary" color="orange" class="w-full">{{ __('actions.save') }}</flux:button>
    </form>
</flux:modal>
