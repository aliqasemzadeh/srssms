<?php

use App\Enums\Support\TicketPriorityEnum;
use App\Livewire\Concerns\AuthorizesAdministratorPermissions;
use App\Models\Support\Ticket;
use Flux\Flux;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use AuthorizesAdministratorPermissions;
    use WithPagination;

    public string $search = '';

    public string $priority = '';

    public string $sortBy = 'last_replied_at';

    public string $sortDirection = 'desc';

    #[Computed]
    public function tickets(): LengthAwarePaginator
    {
        $allowedSorts = ['created_at', 'last_replied_at', 'priority', 'title'];
        $sortBy = in_array($this->sortBy, $allowedSorts, true) ? $this->sortBy : 'last_replied_at';
        $sortDirection = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        return Ticket::query()
            ->needsAttention()
            ->with(['user' => fn ($query) => $query->withTrashed()])
            ->withCount('replies')
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('title', 'like', "%{$this->search}%")
                        ->orWhereHas('user', function ($query) {
                            $query->withTrashed()
                                ->where(function ($query) {
                                    $query->where('first_name', 'like', "%{$this->search}%")
                                        ->orWhere('last_name', 'like', "%{$this->search}%")
                                        ->orWhere('email', 'like', "%{$this->search}%")
                                        ->orWhere('mobile', 'like', "%{$this->search}%")
                                        ->orWhere('username', 'like', "%{$this->search}%");
                                });
                        });
                });
            })
            ->when($this->priority, fn ($query) => $query->where('priority', $this->priority))
            ->orderBy($sortBy, $sortDirection)
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

    public function delete(int $ticketId): void
    {
        $this->authorizePermission('support-system.ticket.delete');

        $ticket = Ticket::query()->findOrFail($ticketId);
        $ticket->delete();

        unset($this->tickets);
        $this->resetPage();

        Flux::toast(__('general.ticket_deleted'));
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPriority(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['priority']);
        $this->resetPage();
    }

    #[On('panels.administrator.support-system.ticket.new.refresh')]
    public function refresh(): void
    {
        unset($this->tickets);
    }
};
?>

<div>
    <x-slot name="title">{{ __('general.new_tickets') }} - {{ config('app.name') }}</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('panels.administrator.dashboard.index') }}" icon="home" wire:navigate />
                <flux:breadcrumbs.item>{{ __('general.support_system') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ __('general.new_tickets') }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
        </div>

        <flux:card class="space-y-4">
            @php
                $activeFilters = collect([
                    $priority,
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
                        <flux:select wire:model.live="priority" variant="listbox" searchable placeholder="{{ __('general.ticket_priority') }}..." clearable>
                            @foreach (TicketPriorityEnum::cases() as $priorityOption)
                                <flux:select.option value="{{ $priorityOption->value }}">{{ $priorityOption->label() }}</flux:select.option>
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

                <flux:tooltip content="{{ __('general.refresh') }}">
                    <flux:button size="sm" variant="ghost" icon="refresh-cw" wire:click="refresh" class="shrink-0" />
                </flux:tooltip>
            </div>

            <flux:table :paginate="$this->tickets">
                <flux:table.columns>
                    <flux:table.column sortable :sorted="$sortBy === 'title'" :direction="$sortDirection" wire:click="sort('title')">{{ __('general.ticket_title') }}</flux:table.column>
                    <flux:table.column>{{ __('general.user') }}</flux:table.column>
                    <flux:table.column>{{ __('general.ticket_status') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'priority'" :direction="$sortDirection" wire:click="sort('priority')">{{ __('general.ticket_priority') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'last_replied_at'" :direction="$sortDirection" wire:click="sort('last_replied_at')">{{ __('general.last_replied_at') }}</flux:table.column>
                    <flux:table.column align="end">{{ __('general.actions') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($this->tickets as $ticket)
                        <flux:table.row :key="$ticket->id">
                            <flux:table.cell>
                                <a href="{{ route('panels.administrator.support-system.ticket.view', $ticket) }}" class="font-medium text-sky-600 hover:underline dark:text-sky-400" wire:navigate>
                                    {{ $ticket->title }}
                                </a>
                                <div class="text-xs text-zinc-500">#{{ $ticket->id }} · {{ $ticket->replies_count }} {{ __('general.ticket_replies') }}</div>
                            </flux:table.cell>
                            <flux:table.cell>{{ $ticket->user?->full_name ?? '—' }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" :color="$ticket->status->color()">{{ $ticket->status->label() }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" :color="$ticket->priority->color()">{{ $ticket->priority->label() }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ $ticket->last_replied_at?->toDynamicFormat('Y/m/d H:i') ?? '—' }}</flux:table.cell>
                            <flux:table.cell align="end">
                                <div class="flex justify-end gap-2">
                                    <flux:tooltip content="{{ __('general.view') }}">
                                        <flux:button size="xs" variant="primary" color="sky" icon="eye" icon:variant="outline" :href="route('panels.administrator.support-system.ticket.view', $ticket)" wire:navigate />
                                    </flux:tooltip>
                                    @can('support-system.ticket.delete')
                                        <flux:tooltip content="{{ __('general.delete') }}">
                                            <flux:button size="xs" variant="primary" color="red" icon="trash" icon:variant="outline" wire:click="delete({{ $ticket->id }})" wire:confirm="{{ __('general.are_you_sure') }}" />
                                        </flux:tooltip>
                                    @endcan
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="6" align="center">
                                {{ __('general.no_tickets_found') }}
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>
</div>
