<?php

use App\Models\Phonebook\Contact;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public ?Contact $contact = null;

    #[On('panels.user.phonebook.contact.delete.assign-data')]
    public function assignData(int $contact): void
    {
        $this->contact = Contact::query()->ownedBy(Auth::user())->findOrFail($contact);
        Flux::modal('phonebook.contact.delete')->show();
    }

    public function delete(): void
    {
        if (! $this->contact) {
            return;
        }

        $this->contact->delete();
        $this->contact = null;
        $this->dispatch('panels.user.phonebook.index.refresh');
        Flux::modals()->close();
        Flux::toast(__('general.contact_deleted'));
    }
};
?>

<flux:modal name="phonebook.contact.delete" class="min-w-[22rem] space-y-6">
    <div>
        <flux:heading size="lg">{{ __('general.delete_confirmation') }}</flux:heading>
        <flux:text class="mt-2">
            {{ __('general.delete_warning_message') }}<br>
            {{ __('general.action_cannot_be_reversed') }}
        </flux:text>
    </div>

    @if ($contact)
        <flux:callout icon="book-user" variant="secondary" inline>
            <flux:callout.heading>{{ $contact->full_name }} — <span dir="ltr">{{ $contact->mobile }}</span></flux:callout.heading>
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
