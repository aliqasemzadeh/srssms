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
    /** @var array<int, int> */
    public array $contactIds = [];

    /** @var array<int, int|string> */
    public array $group_ids = [];

    #[On('panels.user.phonebook.contact.assign-groups.assign-data')]
    public function assignData(array $contactIds = []): void
    {
        $this->contactIds = collect($contactIds)->map(fn ($id) => (int) $id)->filter()->values()->all();
        $this->group_ids = [];
        $this->resetValidation();
        unset($this->groups);

        Flux::modal('phonebook.contact.assign-groups')->show();
    }

    #[Computed]
    public function groups(): Collection
    {
        return Group::query()->where('user_id', Auth::id())->orderBy('name')->get(['id', 'name']);
    }

    public function save(): void
    {
        $this->validate([
            'group_ids' => ['required', 'array', 'min:1'],
            'group_ids.*' => ['integer'],
            'contactIds' => ['required', 'array', 'min:1'],
        ]);

        $groupIds = Group::query()
            ->where('user_id', Auth::id())
            ->whereIn('id', $this->group_ids)
            ->pluck('id')
            ->all();

        $contacts = Contact::query()
            ->ownedBy(Auth::user())
            ->whereIn('id', $this->contactIds)
            ->get();

        foreach ($contacts as $contact) {
            $contact->groups()->syncWithoutDetaching($groupIds);
        }

        $this->dispatch('panels.user.phonebook.index.refresh');
        Flux::modals()->close();
        Flux::toast(__('general.groups_assigned'));
    }
};
?>

<flux:modal name="phonebook.contact.assign-groups" flyout position="right" class="space-y-6">
    <div>
        <flux:heading size="lg">{{ __('general.assign_groups') }}</flux:heading>
        <flux:subheading>{{ __('general.assign_groups_hint', ['count' => count($contactIds)]) }}</flux:subheading>
    </div>

    <form wire:submit="save" class="space-y-6">
        <flux:select wire:model="group_ids" variant="listbox" searchable multiple label="{{ __('general.phonebook_groups') }}">
            @foreach ($this->groups as $group)
                <flux:select.option value="{{ $group->id }}">{{ $group->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:button type="submit" variant="primary" color="teal" class="w-full">{{ __('actions.save') }}</flux:button>
    </form>
</flux:modal>
