<?php

use App\Enums\Phonebook\ContactNoteStatusEnum;
use App\Models\Phonebook\Note;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = '';

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    #[Computed]
    public function notes(): LengthAwarePaginator
    {
        return Note::query()
            ->ownedBy(Auth::user())
            ->with('contact')
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('body', 'like', "%{$this->search}%")
                        ->orWhereHas('contact', function ($q) {
                            $q->where('first_name', 'like', "%{$this->search}%")
                                ->orWhere('last_name', 'like', "%{$this->search}%")
                                ->orWhere('mobile', 'like', "%{$this->search}%");
                        });
                });
            })
            ->when($this->status, fn ($query) => $query->where('status', $this->status))
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

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset([
            'status',
        ]);

        $this->resetPage();
    }

    #[On('panels.user.phonebook.note.index.refresh')]
    public function refresh(): void
    {
        unset($this->notes);
    }
};
?>

<div>
    <x-slot name="title">{{ __('general.notes') }} - {{ config('app.name') }}</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('panels.user.dashboard.index') }}" icon="home" wire:navigate />
                <flux:breadcrumbs.item href="{{ route('panels.user.phonebook.index') }}" wire:navigate>{{ __('general.phonebook') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ __('general.notes') }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>

            <div class="sm:hidden shrink-0">
                <flux:dropdown align="end">
                    <flux:button icon:trailing="chevron-down">{{ __('general.actions') }}</flux:button>
                    <flux:menu>
                        <flux:menu.item
                            icon="plus"
                            wire:click="$dispatch('panels.user.phonebook.note.create.assign-data')"
                        >
                            {{ __('actions.create') }} {{ __('general.note') }}
                        </flux:menu.item>
                    </flux:menu>
                </flux:dropdown>
            </div>

            <div class="hidden items-center gap-2 sm:flex shrink-0">
                <flux:button
                    variant="primary"
                    color="teal"
                    icon="plus"
                    wire:click="$dispatch('panels.user.phonebook.note.create.assign-data')"
                >
                    {{ __('actions.create') }} {{ __('general.note') }}
                </flux:button>
            </div>
        </div>

        <flux:card class="space-y-4">
            @php
                $activeFilters = collect([
                    $status,
                ])->filter(fn ($v) => filled($v))->count();
            @endphp

            <div class="flex items-center gap-3">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    icon="search"
                    placeholder="{{ __('general.search') }}..."
                    clearable
                    class="min-w-0 flex-1 max-w-xs"
                />

                <flux:dropdown align="end" class="shrink-0 ms-auto">
                    <flux:button
                        icon="funnel"
                        icon:variant="micro"
                        icon:class="text-zinc-400"
                    >
                        {{ __('general.filters') }}

                        <x-slot name="iconTrailing">
                            <flux:badge size="sm" class="-mr-1">
                                <span class="tabular-nums">{{ $activeFilters }}</span>
                            </flux:badge>
                        </x-slot>
                    </flux:button>

                    <flux:popover class="w-[min(100vw-2rem,20rem)] max-h-[70vh] overflow-y-auto flex flex-col gap-4">
                        <flux:select wire:model.live="status" variant="listbox" searchable placeholder="{{ __('general.note_status') }}..." clearable>
                            @foreach (ContactNoteStatusEnum::options() as $value => $label)
                                <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                            @endforeach
                        </flux:select>

                        <flux:separator variant="subtle" />

                        <flux:button
                            variant="subtle"
                            size="sm"
                            class="justify-start -m-2 !px-2"
                            wire:click="clearFilters"
                        >
                            {{ __('general.clear_filters') }}
                        </flux:button>
                    </flux:popover>
                </flux:dropdown>
            </div>

            <flux:table :paginate="$this->notes">
                <flux:table.columns>
                    <flux:table.column>{{ __('general.contact') }}</flux:table.column>
                    <flux:table.column>{{ __('general.note') }}</flux:table.column>
                    <flux:table.column>{{ __('general.note_status') }}</flux:table.column>
                    <flux:table.column>{{ __('general.remind_at') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')">{{ __('general.created_at') }}</flux:table.column>
                    <flux:table.column align="end">{{ __('general.actions') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->notes as $note)
                        <flux:table.row :key="$note->id">
                            <flux:table.cell variant="strong">
                                @if ($note->contact)
                                    <a href="{{ route('panels.user.phonebook.view', $note->contact) }}" class="hover:underline" wire:navigate>
                                        {{ $note->contact->full_name }}
                                    </a>
                                @else
                                    —
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>{{ \Illuminate\Support\Str::limit($note->body, 60) }}</flux:table.cell>
                            <flux:table.cell>
                                @if ($note->status)
                                    <flux:badge size="sm" color="{{ $note->status->color() }}">{{ $note->status->label() }}</flux:badge>
                                @else
                                    —
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @if ($note->remind_at)
                                    {{ $note->remind_at->toDynamicFormat('Y/m/d') }}
                                    @if ($note->reminded_at)
                                        <flux:badge size="sm" color="green">{{ __('general.reminded') }}</flux:badge>
                                    @endif
                                @else
                                    —
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>{{ $note->created_at->toDynamicFormat('Y/m/d H:i') }}</flux:table.cell>
                            <flux:table.cell align="end">
                                <div class="flex justify-end gap-2">
                                    <flux:tooltip content="{{ __('general.edit') }}">
                                        <flux:button size="xs" variant="primary" color="blue" icon="pencil" icon:variant="outline" wire:click="$dispatch('panels.user.phonebook.note.edit.assign-data', { note: {{ $note->id }} })" />
                                    </flux:tooltip>
                                    <flux:tooltip content="{{ __('general.delete') }}">
                                        <flux:button size="xs" variant="danger" icon="trash" icon:variant="outline" wire:click="$dispatch('panels.user.phonebook.note.delete.assign-data', { note: {{ $note->id }} })" />
                                    </flux:tooltip>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>

    <livewire:phonebook.note.create :key="'phonebook-note-create'" />
    <livewire:phonebook.note.edit :key="'phonebook-note-edit'" />
    <livewire:phonebook.note.delete :key="'phonebook-note-delete'" />
</div>
