<?php

use App\Enums\Phonebook\ContactPersonTypeEnum;
use App\Exports\Phonebook\ContactsExport;
use App\Models\Phonebook\Contact;
use App\Models\Phonebook\Group;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Tags\Tag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    public string $groupId = '';

    public string $tagId = '';

    public string $personType = '';

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    /** @var array<int, int> */
    public array $selected = [];

    #[Computed]
    public function contacts(): LengthAwarePaginator
    {
        return Contact::query()
            ->ownedBy(Auth::user())
            ->with(['groups', 'tags'])
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('first_name', 'like', "%{$this->search}%")
                        ->orWhere('last_name', 'like', "%{$this->search}%")
                        ->orWhere('mobile', 'like', "%{$this->search}%")
                        ->orWhere('company', 'like', "%{$this->search}%");
                });
            })
            ->when($this->groupId, fn ($query) => $query->whereHas('groups', fn ($q) => $q->where('phonebook_groups.id', $this->groupId)))
            ->when($this->tagId, fn ($query) => $query->withAnyTags([Tag::find($this->tagId)?->name], Contact::tagTypeFor(Auth::user())))
            ->when($this->personType, fn ($query) => $query->where('person_type', $this->personType))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(config('general.per_page', 10));
    }

    #[Computed]
    public function groups(): Collection
    {
        return Group::query()->where('user_id', Auth::id())->orderBy('name')->get(['id', 'name']);
    }

    #[Computed]
    public function tags(): Collection
    {
        return Tag::query()->where('type', Contact::tagTypeFor(Auth::user()))->get();
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

    public function updatedGroupId(): void
    {
        $this->resetPage();
    }

    public function updatedTagId(): void
    {
        $this->resetPage();
    }

    public function updatedPersonType(): void
    {
        $this->resetPage();
    }

    public function toggleSelect(int $id): void
    {
        if (in_array($id, $this->selected, true)) {
            $this->selected = array_values(array_filter($this->selected, fn ($item) => (int) $item !== $id));
        } else {
            $this->selected[] = $id;
        }
    }

    public function selectPage(): void
    {
        $pageIds = $this->contacts->pluck('id')->map(fn ($id) => (int) $id)->all();
        $this->selected = array_values(array_unique([...$this->selected, ...$pageIds]));
    }

    public function clearSelection(): void
    {
        $this->selected = [];
    }

    public function export()
    {
        $groupId = $this->groupId !== '' ? (int) $this->groupId : null;

        return Excel::download(
            new ContactsExport(Auth::user(), $groupId),
            'contacts-'.now()->format('Ymd-His').'.xlsx'
        );
    }

    public function goToSendSms(): mixed
    {
        if ($this->selected === []) {
            \Flux\Flux::toast(__('general.select_contacts_first'));

            return null;
        }

        return $this->redirect(route('panels.user.sms.send', [
            'contacts' => implode(',', $this->selected),
        ]), navigate: true);
    }

    #[On('panels.user.phonebook.index.refresh')]
    public function refresh(): void
    {
        unset($this->contacts, $this->groups, $this->tags);
    }
};
?>

<div
    x-data="{
        cookieName: 'phonebook_sms_selection',
        syncCookie(ids) {
            const value = encodeURIComponent(JSON.stringify(ids || []));
            document.cookie = `${this.cookieName}=${value};path=/;max-age=${60 * 60 * 24 * 7};SameSite=Lax`;
        }
    }"
    x-effect="syncCookie(@js($selected))"
>
    <x-slot name="title">{{ __('general.phonebook') }} - {{ config('app.name') }}</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('panels.user.dashboard.index') }}" icon="home" wire:navigate />
                <flux:breadcrumbs.item>{{ __('general.phonebook') }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>

            <div class="flex flex-wrap gap-2">
                <flux:tooltip content="{{ __('actions.import') }}">
                    <flux:button size="sm" variant="primary" color="teal" icon="upload" icon:variant="outline" wire:click="$dispatch('panels.user.phonebook.contact.import.assign-data')" />
                </flux:tooltip>
                <flux:tooltip content="{{ __('actions.export') }}">
                    <flux:button size="sm" variant="primary" color="cyan" icon="download" icon:variant="outline" wire:click="export" />
                </flux:tooltip>
                <flux:button variant="primary" color="teal" icon="plus" wire:click="$dispatch('panels.user.phonebook.contact.create.assign-data')">
                    {{ __('actions.create') }} {{ __('general.contact') }}
                </flux:button>
            </div>
        </div>

        @if (count($selected) > 0)
            <flux:callout icon="send" variant="secondary" inline>
                <flux:callout.heading>
                    {{ __('general.selected_contacts_count', ['count' => count($selected)]) }}
                </flux:callout.heading>
                <x-slot:actions>
                    <flux:button size="sm" variant="primary" color="violet" wire:click="$dispatch('panels.user.phonebook.contact.assign-groups.assign-data', { contactIds: @js($selected) })">
                        {{ __('general.assign_groups') }}
                    </flux:button>
                    <flux:button size="sm" variant="primary" color="sky" icon="send" wire:click="goToSendSms">
                        {{ __('general.send_sms') }}
                    </flux:button>
                    <flux:button size="sm" variant="ghost" wire:click="clearSelection">{{ __('actions.clear') }}</flux:button>
                </x-slot:actions>
            </flux:callout>
        @endif

        <flux:card>
            <div class="mb-4 grid gap-3 md:grid-cols-4">
                <flux:input wire:model.live.debounce.300ms="search" icon="search" placeholder="{{ __('general.search') }}..." clearable />

                <flux:select wire:model.live="groupId" variant="listbox" searchable placeholder="{{ __('general.phonebook_group') }}..." clearable>
                    @foreach ($this->groups as $group)
                        <flux:select.option value="{{ $group->id }}">{{ $group->name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model.live="tagId" variant="listbox" searchable placeholder="{{ __('general.phonebook_tag') }}..." clearable>
                    @foreach ($this->tags as $tag)
                        <flux:select.option value="{{ $tag->id }}">{{ $tag->name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model.live="personType" variant="listbox" searchable placeholder="{{ __('general.person_type') }}..." clearable>
                    @foreach (ContactPersonTypeEnum::options() as $value => $label)
                        <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <div class="mb-3">
                <flux:button size="xs" variant="ghost" wire:click="selectPage">{{ __('general.select_page') }}</flux:button>
            </div>

            <flux:table :paginate="$this->contacts">
                <flux:table.columns>
                    <flux:table.column></flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'first_name'" :direction="$sortDirection" wire:click="sort('first_name')">{{ __('general.name') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'mobile'" :direction="$sortDirection" wire:click="sort('mobile')">{{ __('general.mobile') }}</flux:table.column>
                    <flux:table.column>{{ __('general.company') }}</flux:table.column>
                    <flux:table.column>{{ __('general.phonebook_groups') }}</flux:table.column>
                    <flux:table.column>{{ __('general.phonebook_tags') }}</flux:table.column>
                    <flux:table.column align="end">{{ __('general.actions') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->contacts as $contact)
                        <flux:table.row :key="$contact->id">
                            <flux:table.cell>
                                <flux:checkbox
                                    wire:click="toggleSelect({{ $contact->id }})"
                                    :checked="in_array($contact->id, $selected, true)"
                                />
                            </flux:table.cell>
                            <flux:table.cell variant="strong">
                                <a href="{{ route('panels.user.phonebook.view', $contact) }}" class="hover:underline" wire:navigate>
                                    {{ $contact->full_name }}
                                </a>
                            </flux:table.cell>
                            <flux:table.cell><span dir="ltr">{{ $contact->mobile }}</span></flux:table.cell>
                            <flux:table.cell>{{ $contact->company ?: '—' }}</flux:table.cell>
                            <flux:table.cell>
                                <div class="flex flex-wrap gap-1">
                                    @forelse ($contact->groups as $group)
                                        <flux:badge size="sm" color="teal">{{ $group->name }}</flux:badge>
                                    @empty
                                        <span class="text-zinc-400">—</span>
                                    @endforelse
                                </div>
                            </flux:table.cell>
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
                                    <flux:tooltip content="{{ __('general.delete') }}">
                                        <flux:button size="xs" variant="danger" icon="trash" icon:variant="outline" wire:click="$dispatch('panels.user.phonebook.contact.delete.assign-data', { contact: {{ $contact->id }} })" />
                                    </flux:tooltip>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>

    <livewire:phonebook.contact.create :key="'phonebook-contact-create'" />
    <livewire:phonebook.contact.edit :key="'phonebook-contact-edit'" />
    <livewire:phonebook.contact.delete :key="'phonebook-contact-delete'" />
    <livewire:phonebook.contact.import :key="'phonebook-contact-import'" />
    <livewire:phonebook.contact.assign-groups :key="'phonebook-contact-assign-groups'" />
</div>
