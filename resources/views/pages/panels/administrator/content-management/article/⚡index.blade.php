<?php

use App\Models\Content\Article;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Tags\Tag;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    public string $tagId = '';

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    #[Computed]
    public function articles(): LengthAwarePaginator
    {
        return Article::query()
            ->with(['user', 'tags'])
            ->when($this->search, function ($query) {
                $query->where('title', 'like', "%{$this->search}%");
            })
            ->when($this->tagId, function ($query) {
                $tag = Tag::query()->find($this->tagId);

                if ($tag) {
                    $query->withAnyTags([$tag], Article::tagType());
                }
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(config('general.per_page', 10));
    }

    #[Computed]
    public function tags(): Collection
    {
        return Tag::query()
            ->where('type', Article::tagType())
            ->orderBy('order_column')
            ->get();
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

    public function updatedTagId(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['tagId']);
        $this->resetPage();
    }

    #[On('panels.administrator.content-management.article.index.refresh')]
    public function refresh(): void
    {
        unset($this->articles, $this->tags);
    }
};
?>

<div>
    <x-slot name="title">{{ __('general.articles') }} - {{ config('app.name') }}</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('panels.administrator.dashboard.index') }}" icon="home" wire:navigate />
                <flux:breadcrumbs.item>{{ __('general.content_management') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ __('general.articles') }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>

            @can('content-management.article.create')
            <div class="sm:hidden shrink-0">
                <flux:dropdown align="end">
                    <flux:button icon:trailing="chevron-down">{{ __('general.actions') }}</flux:button>
                    <flux:menu>
                        <flux:menu.item
                            icon="plus"
                            wire:click="$dispatch('panels.administrator.content-management.article.create.assign-data')"
                        >
                            {{ __('actions.create') }} {{ __('general.article') }}
                        </flux:menu.item>
                    </flux:menu>
                </flux:dropdown>
            </div>

            <div class="hidden items-center gap-2 sm:flex shrink-0">
                <flux:button
                    variant="primary"
                    color="teal"
                    icon="plus"
                    wire:click="$dispatch('panels.administrator.content-management.article.create.assign-data')"
                >
                    {{ __('actions.create') }} {{ __('general.article') }}
                </flux:button>
            </div>
            @endcan
        </div>

        <flux:card class="space-y-4">
            @php
                $activeFilters = collect([
                    $tagId,
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
                        <flux:select wire:model.live="tagId" variant="listbox" searchable placeholder="{{ __('general.article_tags') }}..." clearable>
                            @foreach ($this->tags as $tag)
                                <flux:select.option value="{{ $tag->id }}">{{ $tag->name }}</flux:select.option>
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

            <flux:table :paginate="$this->articles">
                <flux:table.columns>
                    <flux:table.column sortable :sorted="$sortBy === 'title'" :direction="$sortDirection" wire:click="sort('title')">{{ __('general.article_title') }}</flux:table.column>
                    <flux:table.column>{{ __('general.article_tags') }}</flux:table.column>
                    <flux:table.column>{{ __('general.article_author') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')">{{ __('general.created_at') }}</flux:table.column>
                    <flux:table.column align="end">{{ __('general.actions') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->articles as $article)
                        <flux:table.row :key="$article->id">
                            <flux:table.cell variant="strong">{{ $article->title }}</flux:table.cell>
                            <flux:table.cell>
                                <div class="flex flex-wrap gap-1">
                                    @forelse ($article->tags as $tag)
                                        <flux:badge size="sm" color="sky">{{ $tag->name }}</flux:badge>
                                    @empty
                                        <span class="text-zinc-400">—</span>
                                    @endforelse
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>{{ $article->user?->full_name ?: '—' }}</flux:table.cell>
                            <flux:table.cell>{{ $article->created_at->toDynamicFormat('Y/m/d H:i:s') }}</flux:table.cell>
                            <flux:table.cell align="end">
                                <div class="flex justify-end gap-2">
                                    @can('content-management.article.edit')
                                    <flux:tooltip content="{{ __('general.edit') }}">
                                        <flux:button size="xs" variant="primary" color="blue" icon="pencil" icon:variant="outline" wire:click="$dispatch('panels.administrator.content-management.article.edit.assign-data', { article: {{ $article->id }} })" />
                                    </flux:tooltip>
                                    @endcan
                                    @can('content-management.article.delete')
                                    <flux:tooltip content="{{ __('general.delete') }}">
                                        <flux:button size="xs" variant="danger" icon="trash" icon:variant="outline" wire:click="$dispatch('panels.administrator.content-management.article.delete.assign-data', { article: {{ $article->id }} })" />
                                    </flux:tooltip>
                                    @endcan
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>

    <livewire:content-management.article.create :key="'article-create'" />
    <livewire:content-management.article.edit :key="'article-edit'" />
    <livewire:content-management.article.delete :key="'article-delete'" />
</div>
