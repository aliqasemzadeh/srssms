<?php

use App\Models\Sms\Provider;
use App\Services\Sms\SmsManager;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    public string $driver = '';

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    #[Computed]
    public function providers(): LengthAwarePaginator
    {
        return Provider::query()
            ->withCount('gateways')
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('name', 'like', "%{$this->search}%")
                        ->orWhere('driver', 'like', "%{$this->search}%");
                });
            })
            ->when($this->driver, fn ($query) => $query->where('driver', $this->driver))
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

    public function updatedDriver(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset([
            'driver',
        ]);

        $this->resetPage();
    }

    #[On('panels.administrator.sms-management.provider.index.refresh')]
    public function refresh(): void
    {
        unset($this->providers);
    }
};
?>

@php
    $driverOptions = app(SmsManager::class)->driverOptions();
@endphp

<div>
    <x-slot name="title">{{ __('general.providers') }} - {{ config('app.name') }}</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('panels.administrator.dashboard.index') }}" icon="home" wire:navigate />
                <flux:breadcrumbs.item>{{ __('general.sms_management') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ __('general.providers') }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>

            @can('sms-management.provider.create')
            <div class="sm:hidden shrink-0">
                <flux:dropdown align="end">
                    <flux:button icon:trailing="chevron-down">{{ __('general.actions') }}</flux:button>
                    <flux:menu>
                        <flux:menu.item
                            icon="plus"
                            wire:click="$dispatch('panels.administrator.sms-management.provider.create.assign-data')"
                        >
                            {{ __('actions.create') }} {{ __('general.provider') }}
                        </flux:menu.item>
                    </flux:menu>
                </flux:dropdown>
            </div>

            <div class="hidden items-center gap-2 sm:flex shrink-0">
                <flux:button
                    variant="primary"
                    color="teal"
                    icon="plus"
                    wire:click="$dispatch('panels.administrator.sms-management.provider.create.assign-data')"
                >
                    {{ __('actions.create') }} {{ __('general.provider') }}
                </flux:button>
            </div>
            @endcan
        </div>

        <flux:card class="space-y-4">
            @php
                $activeFilters = collect([
                    $driver,
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
                        <flux:select wire:model.live="driver" variant="listbox" searchable placeholder="{{ __('general.driver') }}..." clearable>
                            @foreach ($driverOptions as $value => $label)
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

            <flux:table :paginate="$this->providers">
                <flux:table.columns>
                    <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection" wire:click="sort('name')">{{ __('general.name') }}</flux:table.column>
                    <flux:table.column>{{ __('general.driver') }}</flux:table.column>
                    <flux:table.column>{{ __('general.sms_gateways') }}</flux:table.column>
                    <flux:table.column>{{ __('general.status') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')">{{ __('general.created_at') }}</flux:table.column>
                    <flux:table.column align="end">{{ __('general.actions') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->providers as $provider)
                        <flux:table.row :key="$provider->id">
                            <flux:table.cell variant="strong">{{ $provider->name }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="sky">{{ $driverOptions[$provider->driver] ?? $provider->driver }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="zinc">{{ $provider->gateways_count }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="{{ $provider->is_active ? 'green' : 'red' }}">
                                    {{ $provider->is_active ? __('general.active') : __('general.inactive') }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ $provider->created_at->toDynamicFormat('Y/m/d H:i:s') }}</flux:table.cell>
                            <flux:table.cell align="end">
                                <div class="flex justify-end gap-2">
                                    @can('sms-management.provider.edit')
                                    <flux:tooltip content="{{ __('general.edit') }}">
                                        <flux:button size="xs" variant="primary" color="blue" icon="pencil" icon:variant="outline" wire:click="$dispatch('panels.administrator.sms-management.provider.edit.assign-data', { provider: {{ $provider->id }} })" />
                                    </flux:tooltip>
                                    @endcan
                                    @can('sms-management.provider.delete')
                                    <flux:tooltip content="{{ __('general.delete') }}">
                                        <flux:button size="xs" variant="danger" icon="trash" icon:variant="outline" wire:click="$dispatch('panels.administrator.sms-management.provider.delete.assign-data', { provider: {{ $provider->id }} })" />
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

    <livewire:sms-management.provider.create :key="'provider-create'" />
    <livewire:sms-management.provider.edit :key="'provider-edit'" />
    <livewire:sms-management.provider.delete :key="'provider-delete'" />
</div>
