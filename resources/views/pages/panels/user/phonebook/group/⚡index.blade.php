<?php

use App\Models\Phonebook\Group;
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

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    #[Computed]
    public function groups(): LengthAwarePaginator
    {
        return Group::query()
            ->where('user_id', Auth::id())
            ->withCount('contacts')
            ->when($this->search, fn ($query) => $query->where('name', 'like', "%{$this->search}%"))
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

    #[On('panels.user.phonebook.group.index.refresh')]
    public function refresh(): void
    {
        unset($this->groups);
    }
};
?>

<div>
    <x-slot name="title">{{ __('general.phonebook_groups') }} - {{ config('app.name') }}</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('panels.user.dashboard.index') }}" icon="home" wire:navigate />
                <flux:breadcrumbs.item href="{{ route('panels.user.phonebook.index') }}" wire:navigate>{{ __('general.phonebook') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ __('general.phonebook_groups') }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>

            <div class="sm:hidden shrink-0">
                <flux:dropdown align="end">
                    <flux:button icon:trailing="chevron-down">{{ __('general.actions') }}</flux:button>
                    <flux:menu>
                        <flux:menu.item icon="plus" wire:click="$dispatch('panels.user.phonebook.group.create.assign-data')">
                            {{ __('actions.create') }} {{ __('general.phonebook_group') }}
                        </flux:menu.item>
                    </flux:menu>
                </flux:dropdown>
            </div>

            <div class="hidden items-center gap-2 sm:flex shrink-0">
                <flux:button variant="primary" color="teal" icon="plus" wire:click="$dispatch('panels.user.phonebook.group.create.assign-data')">
                    {{ __('actions.create') }} {{ __('general.phonebook_group') }}
                </flux:button>
            </div>
        </div>

        <flux:card>
            <div class="mb-4">
                <flux:input wire:model.live.debounce.300ms="search" icon="search" placeholder="{{ __('general.search') }}..." clearable class="min-w-0 flex-1 max-w-xs" />
            </div>

            <flux:table :paginate="$this->groups">
                <flux:table.columns>
                    <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection" wire:click="sort('name')">{{ __('general.name') }}</flux:table.column>
                    <flux:table.column>{{ __('general.description') }}</flux:table.column>
                    <flux:table.column>{{ __('general.contacts') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')">{{ __('general.created_at') }}</flux:table.column>
                    <flux:table.column align="end">{{ __('general.actions') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->groups as $group)
                        <flux:table.row :key="$group->id">
                            <flux:table.cell variant="strong">
                                <a href="{{ route('panels.user.phonebook.group.view', $group) }}" class="hover:underline" wire:navigate>
                                    {{ $group->name }}
                                </a>
                            </flux:table.cell>
                            <flux:table.cell>{{ $group->description ?: '—' }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="teal">{{ $group->contacts_count }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ $group->created_at->toDynamicFormat('Y/m/d H:i') }}</flux:table.cell>
                            <flux:table.cell align="end">
                                <div class="flex justify-end gap-2">
                                    <flux:tooltip content="{{ __('general.view') }}">
                                        <flux:button size="xs" variant="primary" color="zinc" icon="eye" icon:variant="outline" :href="route('panels.user.phonebook.group.view', $group)" wire:navigate />
                                    </flux:tooltip>
                                    <flux:tooltip content="{{ __('general.edit') }}">
                                        <flux:button size="xs" variant="primary" color="blue" icon="pencil" icon:variant="outline" wire:click="$dispatch('panels.user.phonebook.group.edit.assign-data', { group: {{ $group->id }} })" />
                                    </flux:tooltip>
                                    <flux:tooltip content="{{ __('general.delete') }}">
                                        <flux:button size="xs" variant="danger" icon="trash" icon:variant="outline" wire:click="$dispatch('panels.user.phonebook.group.delete.assign-data', { group: {{ $group->id }} })" />
                                    </flux:tooltip>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>

    <livewire:phonebook.group.create :key="'phonebook-group-create'" />
    <livewire:phonebook.group.edit :key="'phonebook-group-edit'" />
    <livewire:phonebook.group.delete :key="'phonebook-group-delete'" />
</div>
