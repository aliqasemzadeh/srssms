<?php

use App\Models\Finance\Currency;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    public string $type = '';

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    #[Computed]
    public function currencies(): LengthAwarePaginator
    {
        return Currency::query()
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('name', 'like', "%{$this->search}%")
                        ->orWhere('symbol', 'like', "%{$this->search}%");
                });
            })
            ->when($this->type, fn ($query) => $query->where('type', $this->type))
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

    public function updatedType(): void
    {
        $this->resetPage();
    }

    #[On('panels.administrator.finance-management.currency.index.refresh')]
    public function refresh(): void
    {
        unset($this->currencies);
    }
};
?>

<div>
    <x-slot name="title">{{ __('general.currencies') }} - {{ config('app.name') }}</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('panels.administrator.dashboard.index') }}" icon="home" />
                <flux:breadcrumbs.item>{{ __('general.finance_management') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ __('general.currencies') }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>

            <flux:button class="shrink-0" variant="primary" color="teal" icon="plus" wire:click="$dispatch('panels.administrator.finance-management.currency.create.assign-data')">
                {{ __('actions.create') }} {{ __('general.currency') }}
            </flux:button>
        </div>

        <flux:card>
            <div class="mb-4 grid gap-3 md:grid-cols-2">
                <flux:input wire:model.live.debounce.300ms="search" icon="search" placeholder="{{ __('general.search') }}..." clearable />

                <flux:select wire:model.live="type" variant="listbox" searchable placeholder="{{ __('general.type') }}..." clearable>
                    <flux:select.option value="fiat">{{ __('general.currency_type_fiat') }}</flux:select.option>
                    <flux:select.option value="crypto">{{ __('general.currency_type_crypto') }}</flux:select.option>
                    <flux:select.option value="commodity">{{ __('general.currency_type_commodity') }}</flux:select.option>
                </flux:select>
            </div>

            <flux:table :paginate="$this->currencies">
                <flux:table.columns>
                    <flux:table.column sortable :sorted="$sortBy === 'symbol'" :direction="$sortDirection" wire:click="sort('symbol')">{{ __('general.symbol') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection" wire:click="sort('name')">{{ __('general.name') }}</flux:table.column>
                    <flux:table.column>{{ __('general.type') }}</flux:table.column>
                    <flux:table.column>{{ __('general.decimals') }}</flux:table.column>
                    <flux:table.column>{{ __('general.status') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')">{{ __('general.created_at') }}</flux:table.column>
                    <flux:table.column align="end">{{ __('general.actions') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->currencies as $currency)
                        <flux:table.row :key="$currency->id">
                            <flux:table.cell variant="strong">
                                <div class="flex items-center gap-2">
                                    @if ($currency->logo)
                                        <img src="{{ asset('storage/' . $currency->logo) }}" alt="{{ $currency->name }}" class="size-6 rounded object-contain" />
                                    @else
                                        <flux:icon.circle-dollar-sign variant="outline" class="size-4 text-teal-500" />
                                    @endif
                                    <span dir="ltr">{{ $currency->symbol }}</span>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>{{ $currency->name }}</flux:table.cell>
                            <flux:table.cell>
                                @php
                                    $typeColor = match ($currency->type) {
                                        'crypto' => 'amber',
                                        'commodity' => 'emerald',
                                        default => 'blue',
                                    };
                                @endphp
                                <flux:badge size="sm" color="{{ $typeColor }}">{{ __('general.currency_type_' . $currency->type) }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="zinc">{{ $currency->decimals }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="{{ $currency->is_active ? 'green' : 'red' }}">
                                    {{ $currency->is_active ? __('general.active') : __('general.inactive') }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ $currency->created_at->toDynamicFormat('Y/m/d H:i:s') }}</flux:table.cell>
                            <flux:table.cell align="end">
                                <div class="flex justify-end gap-2">
                                    <flux:tooltip content="{{ __('general.edit') }}">
                                        <flux:button size="xs" variant="primary" color="blue" icon="pencil" icon:variant="outline" wire:click="$dispatch('panels.administrator.finance-management.currency.edit.assign-data', { currency: {{ $currency->id }} })" />
                                    </flux:tooltip>
                                    <flux:tooltip content="{{ __('general.delete') }}">
                                        <flux:button size="xs" variant="danger" icon="trash" icon:variant="outline" wire:click="$dispatch('panels.administrator.finance-management.currency.delete.assign-data', { currency: {{ $currency->id }} })" />
                                    </flux:tooltip>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>

    <livewire:finance-management.currency.create :key="'currency-create'" />
    <livewire:finance-management.currency.edit :key="'currency-edit'" />
    <livewire:finance-management.currency.delete :key="'currency-delete'" />
</div>
