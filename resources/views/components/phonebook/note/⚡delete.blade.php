<?php

use App\Models\Phonebook\Note;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public ?Note $note = null;

    #[On('panels.user.phonebook.note.delete.assign-data')]
    public function assignData(int $note): void
    {
        $this->note = Note::query()->ownedBy(Auth::user())->findOrFail($note);
        Flux::modal('phonebook.note.delete')->show();
    }

    public function delete(): void
    {
        if (! $this->note) {
            return;
        }

        $this->note->delete();
        $this->note = null;
        $this->dispatch('panels.user.phonebook.note.index.refresh');
        $this->dispatch('panels.user.phonebook.view.refresh');
        Flux::modals()->close();
        Flux::toast(__('general.note_deleted'));
    }
};
?>

<flux:modal name="phonebook.note.delete" class="min-w-[22rem] space-y-6">
    <div>
        <flux:heading size="lg">{{ __('general.delete_confirmation') }}</flux:heading>
        <flux:text class="mt-2">
            {{ __('general.delete_warning_message') }}<br>
            {{ __('general.action_cannot_be_reversed') }}
        </flux:text>
    </div>

    @if ($note)
        <flux:callout icon="sticky-note" variant="secondary" inline>
            <flux:callout.heading>{{ \Illuminate\Support\Str::limit($note->body, 80) }}</flux:callout.heading>
        </flux:callout>
    @endif

    <div class="flex gap-2">
        <flux:spacer />
        <flux:modal.close>
            <flux:button variant="ghost">{{ __('actions.cancel') }}</flux:button>
        </flux:modal.close>
        <flux:button wire:click="delete" variant="danger" icon="trash" icon:variant="outline">{{ __('actions.delete') }}</flux:button>
    </div>
</flux:modal>
