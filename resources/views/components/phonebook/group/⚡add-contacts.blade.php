<?php

use App\Models\Phonebook\Contact;
use App\Models\Phonebook\Group;
use Flux\Flux;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public ?Group $group = null;

    /** @var array<int, int|string> */
    public array $contact_ids = [];

    #[On('panels.user.phonebook.group.add-contacts.assign-data')]
    public function assignData(int $group): void
    {
        $this->group = Group::query()
            ->where('user_id', Auth::id())
            ->findOrFail($group);

        $this->contact_ids = [];
        $this->resetValidation();
        unset($this->availableContacts);

        Flux::modal('phonebook.group.add-contacts')->show();
    }

    #[Computed]
    public function availableContacts(): Collection
    {
        if (! $this->group) {
            return collect();
        }

        return Contact::query()
            ->ownedBy(Auth::user())
            ->whereDoesntHave('groups', fn ($query) => $query->where('phonebook_groups.id', $this->group->id))
            ->orderBy('first_name')
            ->limit(500)
            ->get(['id', 'first_name', 'last_name', 'mobile']);
    }

    public function save(): void
    {
        $this->validate([
            'contact_ids' => ['required', 'array', 'min:1'],
            'contact_ids.*' => ['integer'],
        ], [], [
            'contact_ids' => __('general.contacts'),
        ]);

        if (! $this->group || $this->group->user_id !== Auth::id()) {
            return;
        }

        $contactIds = Contact::query()
            ->ownedBy(Auth::user())
            ->whereIn('id', $this->contact_ids)
            ->pluck('id')
            ->all();

        $this->group->contacts()->syncWithoutDetaching($contactIds);

        $this->contact_ids = [];
        $this->dispatch('panels.user.phonebook.group.view.refresh');
        $this->dispatch('panels.user.phonebook.group.index.refresh');
        Flux::modals()->close();
        Flux::toast(__('general.contacts_added_to_group'));
    }
};
?>

<flux:modal name="phonebook.group.add-contacts" flyout position="right" class="space-y-6">
    <div>
        <flux:heading size="lg">{{ __('general.add_contacts_to_group') }}</flux:heading>
        <flux:subheading>{{ $group?->name }}</flux:subheading>
    </div>

    <form wire:submit="save" class="space-y-6">
        @if ($this->availableContacts->isEmpty())
            <flux:callout icon="users" variant="secondary">
                <flux:callout.heading>{{ __('general.no_contacts_to_add') }}</flux:callout.heading>
            </flux:callout>
        @else
            <flux:select wire:model="contact_ids" variant="listbox" searchable multiple label="{{ __('general.contacts') }}">
                @foreach ($this->availableContacts as $contact)
                    <flux:select.option value="{{ $contact->id }}">
                        {{ $contact->full_name }} — <span dir="ltr">{{ $contact->mobile }}</span>
                    </flux:select.option>
                @endforeach
            </flux:select>
        @endif

        <flux:button type="submit" variant="primary" color="teal" class="w-full" :disabled="$this->availableContacts->isEmpty()">
            {{ __('actions.save') }}
        </flux:button>
    </form>
</flux:modal>
