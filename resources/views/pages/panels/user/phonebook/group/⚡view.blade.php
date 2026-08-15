<?php

use App\Exports\Phonebook\ContactsExport;
use App\Models\Phonebook\Contact;
use App\Models\Phonebook\Group;
use Flux\Flux;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

new class extends Component
{
    use WithPagination;

    public Group $group;

    public string $search = '';

    public string $sortBy = 'first_name';

    public string $sortDirection = 'asc';

    public function mount(Group $group): void
    {
        abort_unless($group->user_id === Auth::id(), 404);

        $this->group = $group;
    }

    #[Computed]
    public function contacts(): LengthAwarePaginator
    {
        return $this->group->contacts()
            ->with(['tags'])
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('first_name', 'like', "%{$this->search}%")
                        ->orWhere('last_name', 'like', "%{$this->search}%")
                        ->orWhere('mobile', 'like', "%{$this->search}%")
                        ->orWhere('company', 'like', "%{$this->search}%");
                });
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(config('general.per_page', 10));
    }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function removeContact(int $contactId): void
    {
        $contact = Contact::query()
            ->ownedBy(Auth::user())
            ->findOrFail($contactId);

        $this->group->contacts()->detach($contact->id);

        unset($this->contacts);
        $this->group->loadCount('contacts');

        Flux::toast(__('general.contact_removed_from_group'));
    }

    public function export()
    {
        return Excel::download(
            new ContactsExport(Auth::user(), $this->group->id),
            'group-contacts-'.now()->format('Ymd-His').'.xlsx'
        );
    }

    #[On('panels.user.phonebook.group.view.refresh')]
    #[On('panels.user.phonebook.index.refresh')]
    public function refresh(): void
    {
        $this->group->refresh();
        unset($this->contacts);
    }
};
?>

<div>
    <x-slot name="title">{{ $group->name }} - {{ __('general.phonebook_groups') }}</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('panels.user.dashboard.index') }}" icon="home" wire:navigate />
                <flux:breadcrumbs.item href="{{ route('panels.user.phonebook.index') }}" wire:navigate>{{ __('general.phonebook') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('panels.user.phonebook.group.index') }}" wire:navigate>{{ __('general.phonebook_groups') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ $group->name }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>

            <div class="flex flex-wrap gap-2">
                <flux:button
                    size="sm"
                    variant="primary"
                    color="teal"
                    icon="upload"
                    wire:click="$dispatch('panels.user.phonebook.group.import.assign-data', { group: {{ $group->id }} })"
                >
                    {{ __('actions.import') }}
                </flux:button>
                <flux:button
                    size="sm"
                    variant="primary"
                    color="cyan"
                    icon="download"
                    wire:click="export"
                >
                    {{ __('actions.export') }}
                </flux:button>
                <flux:button
                    size="sm"
                    variant="primary"
                    color="violet"
                    icon="users"
                    wire:click="$dispatch('panels.user.phonebook.group.add-contacts.assign-data', { group: {{ $group->id }} })"
                >
                    {{ __('general.add_contacts_to_group') }}
                </flux:button>
                <flux:button
                    size="sm"
                    variant="primary"
                    color="teal"
                    icon="plus"
                    wire:click="$dispatch('panels.user.phonebook.contact.create.assign-data', { groupId: {{ $group->id }} })"
                >
                    {{ __('actions.create') }} {{ __('general.contact') }}
                </flux:button>
                <flux:button
                    size="sm"
                    variant="primary"
                    color="sky"
                    icon="send"
                    :href="route('panels.user.sms.send', ['groups' => $group->id])"
                    wire:navigate
                >
                    {{ __('general.send_sms') }}
                </flux:button>
            </div>
        </div>

        <flux:card class="space-y-2">
            <flux:heading size="lg">{{ $group->name }}</flux:heading>
            @if ($group->description)
                <flux:text>{{ $group->description }}</flux:text>
            @endif
            <flux:badge size="sm" color="teal">{{ __('general.contacts') }}: {{ $this->contacts->total() }}</flux:badge>
        </flux:card>

        <flux:card>
            <div class="mb-4">
                <flux:input wire:model.live.debounce.300ms="search" icon="search" placeholder="{{ __('general.search') }}..." clearable />
            </div>

            <flux:table :paginate="$this->contacts">
                <flux:table.columns>
                    <flux:table.column sortable :sorted="$sortBy === 'first_name'" :direction="$sortDirection" wire:click="sort('first_name')">{{ __('general.name') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'mobile'" :direction="$sortDirection" wire:click="sort('mobile')">{{ __('general.mobile') }}</flux:table.column>
                    <flux:table.column>{{ __('general.company') }}</flux:table.column>
                    <flux:table.column>{{ __('general.phonebook_tags') }}</flux:table.column>
                    <flux:table.column align="end">{{ __('general.actions') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($this->contacts as $contact)
                        <flux:table.row :key="$contact->id">
                            <flux:table.cell variant="strong">
                                <a href="{{ route('panels.user.phonebook.view', $contact) }}" class="hover:underline" wire:navigate>
                                    {{ $contact->full_name }}
                                </a>
                            </flux:table.cell>
                            <flux:table.cell><span dir="ltr">{{ $contact->mobile }}</span></flux:table.cell>
                            <flux:table.cell>{{ $contact->company ?: '—' }}</flux:table.cell>
                            <flux:table.cell>
                                <div class="flex flex-wrap gap-1">
                                    @forelse ($contact->tags as $tag)
                                        <flux:badge size="sm" color="violet">{{ $tag->name }}</flux:badge>
                                    @empty
                                        <span class="text-zinc-400">—</span>
                                    @endforelse
                                </div>
                            </flux:table.cell>
                            <flux:table.cell align="end">
                                <div class="flex justify-end gap-2">
                                    <flux:tooltip content="{{ __('general.view') }}">
                                        <flux:button size="xs" variant="primary" color="zinc" icon="eye" icon:variant="outline" :href="route('panels.user.phonebook.view', $contact)" wire:navigate />
                                    </flux:tooltip>
                                    <flux:tooltip content="{{ __('general.edit') }}">
                                        <flux:button size="xs" variant="primary" color="blue" icon="pencil" icon:variant="outline" wire:click="$dispatch('panels.user.phonebook.contact.edit.assign-data', { contact: {{ $contact->id }} })" />
                                    </flux:tooltip>
                                    <flux:tooltip content="{{ __('general.remove_from_group') }}">
                                        <flux:button
                                            size="xs"
                                            variant="danger"
                                            icon="user-minus"
                                            icon:variant="outline"
                                            wire:click="removeContact({{ $contact->id }})"
                                            wire:confirm="{{ __('general.are_you_sure') }}"
                                        />
                                    </flux:tooltip>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5">
                                <flux:text>{{ __('general.no_contacts_in_group') }}</flux:text>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>

    <livewire:phonebook.group.import :key="'phonebook-group-import'" />
    <livewire:phonebook.group.add-contacts :key="'phonebook-group-add-contacts'" />
    <livewire:phonebook.contact.create :key="'phonebook-group-view-contact-create'" />
    <livewire:phonebook.contact.edit :key="'phonebook-group-view-contact-edit'" />
</div>
