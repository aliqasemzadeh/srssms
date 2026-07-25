<?php

use App\Models\Phonebook\Contact;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Tags\Tag;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    #[Computed]
    public function tags(): LengthAwarePaginator
    {
        return Tag::query()
            ->where('type', Contact::tagTypeFor(Auth::user()))
            ->when($this->search, function ($query) {
                $locale = app()->getLocale();
                $query->where("name->{$locale}", 'like', "%{$this->search}%");
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

    #[On('panels.user.phonebook.tag.index.refresh')]
    public function refresh(): void
    {
        unset($this->tags);
    }
};
?>

<div>
    <x-slot name="title">{{ __('general.phonebook_tags') }} - {{ config('app.name') }}</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('panels.user.dashboard.index') }}" icon="home" wire:navigate />
                <flux:breadcrumbs.item href="{{ route('panels.user.phonebook.index') }}" wire:navigate>{{ __('general.phonebook') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ __('general.phonebook_tags') }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>

            <div class="sm:hidden shrink-0">
                <flux:dropdown align="end">
                    <flux:button icon:trailing="chevron-down">{{ __('general.actions') }}</flux:button>
                    <flux:menu>
                        <flux:menu.item icon="plus" wire:click="$dispatch('panels.user.phonebook.tag.create.assign-data')">
                            {{ __('actions.create') }} {{ __('general.phonebook_tag') }}
                        </flux:menu.item>
                    </flux:menu>
                </flux:dropdown>
            </div>

            <div class="hidden items-center gap-2 sm:flex shrink-0">
                <flux:button variant="primary" color="teal" icon="plus" wire:click="$dispatch('panels.user.phonebook.tag.create.assign-data')">
                    {{ __('actions.create') }} {{ __('general.phonebook_tag') }}
                </flux:button>
            </div>
        </div>

        <flux:card>
            <div class="mb-4">
                <flux:input wire:model.live.debounce.300ms="search" icon="search" placeholder="{{ __('general.search') }}..." clearable class="min-w-0 flex-1 max-w-xs" />
            </div>

            <flux:table :paginate="$this->tags">
                <flux:table.columns>
                    <flux:table.column>{{ __('general.name') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')">{{ __('general.created_at') }}</flux:table.column>
                    <flux:table.column align="end">{{ __('general.actions') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->tags as $tag)
                        <flux:table.row :key="$tag->id">
                            <flux:table.cell variant="strong">
                                <flux:badge size="sm" color="violet">{{ $tag->name }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ $tag->created_at?->toDynamicFormat('Y/m/d H:i') }}</flux:table.cell>
                            <flux:table.cell align="end">
                                <div class="flex justify-end gap-2">
                                    <flux:tooltip content="{{ __('general.edit') }}">
                                        <flux:button size="xs" variant="primary" color="blue" icon="pencil" icon:variant="outline" wire:click="$dispatch('panels.user.phonebook.tag.edit.assign-data', { tag: {{ $tag->id }} })" />
                                    </flux:tooltip>
                                    <flux:tooltip content="{{ __('general.delete') }}">
                                        <flux:button size="xs" variant="danger" icon="trash" icon:variant="outline" wire:click="$dispatch('panels.user.phonebook.tag.delete.assign-data', { tag: {{ $tag->id }} })" />
                                    </flux:tooltip>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>

    <livewire:phonebook.tag.create :key="'phonebook-tag-create'" />
    <livewire:phonebook.tag.edit :key="'phonebook-tag-edit'" />
    <livewire:phonebook.tag.delete :key="'phonebook-tag-delete'" />
</div>
