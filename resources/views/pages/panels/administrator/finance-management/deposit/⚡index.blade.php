<?php

use App\Enums\DepositStatusEnum;
use App\Models\Finance\Currency;
use App\Models\Finance\Deposit;
use Flux\DateRange;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = '';

    public string $method = '';

    public string $currencyId = '';

    public string $currencySearch = '';

    public string $amountOperator = '';

    public string $amountValue = '';

    public string $amountMin = '';

    public string $amountMax = '';

    public ?string $dateFrom = null;

    public ?string $dateTo = null;

    public ?DateRange $dateRange = null;

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    #[Computed]
    public function deposits(): LengthAwarePaginator
    {
        $allowedSorts = ['amount', 'fee', 'tax', 'amount_settled', 'created_at', 'status'];

        $sortBy = in_array($this->sortBy, $allowedSorts, true) ? $this->sortBy : 'created_at';
        $sortDirection = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        return Deposit::query()
            ->with([
                'user' => fn ($query) => $query->withTrashed(),
                'wallet.currency' => fn ($query) => $query->withTrashed(),
                'creator',
            ])
            ->withCount('transactions')
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('tracking_code', 'like', "%{$this->search}%")
                        ->orWhere('admin_note', 'like', "%{$this->search}%")
                        ->orWhereHas('user', function ($query) {
                            $query->withTrashed()
                                ->where(function ($query) {
                                    $query->where('first_name', 'like', "%{$this->search}%")
                                        ->orWhere('last_name', 'like', "%{$this->search}%")
                                        ->orWhere('email', 'like', "%{$this->search}%")
                                        ->orWhere('mobile', 'like', "%{$this->search}%")
                                        ->orWhere('username', 'like', "%{$this->search}%");
                                });
                        })
                        ->orWhereHas('transactions', function ($query) {
                            $query->where('description', 'like', "%{$this->search}%");
                        });
                });
            })
            ->when($this->status, fn ($query) => $query->where('status', $this->status))
            ->when($this->method, fn ($query) => $query->where('method', $this->method))
            ->when($this->currencyId, function ($query) {
                $query->whereHas('wallet', fn ($query) => $query->where('currency_id', $this->currencyId));
            })
            ->when($this->amountOperator === 'gt' && filled($this->amountValue), function ($query) {
                $query->where('amount', '>', $this->amountValue);
            })
            ->when($this->amountOperator === 'lt' && filled($this->amountValue), function ($query) {
                $query->where('amount', '<', $this->amountValue);
            })
            ->when($this->amountOperator === 'between' && filled($this->amountMin) && filled($this->amountMax), function ($query) {
                $query->whereBetween('amount', [$this->amountMin, $this->amountMax]);
            })
            ->when(app()->getLocale() === 'fa', function ($query) {
                $query
                    ->when($this->dateFrom, fn ($query) => $query->whereDate('created_at', '>=', $this->dateFrom))
                    ->when($this->dateTo, fn ($query) => $query->whereDate('created_at', '<=', $this->dateTo));
            }, function ($query) {
                $query->when(
                    $this->dateRange && $this->dateRange->start() && $this->dateRange->end(),
                    fn ($query) => $query->whereBetween('created_at', [
                        $this->dateRange->start()->copy()->startOfDay(),
                        $this->dateRange->end()->copy()->endOfDay(),
                    ])
                );
            })
            ->orderBy($sortBy, $sortDirection)
            ->paginate(config('general.per_page', 10));
    }

    #[Computed]
    public function currencies(): Collection
    {
        $currencies = Currency::query()
            ->withTrashed()
            ->when($this->currencySearch, function ($query) {
                $query->where(function ($query) {
                    $query->where('name', 'like', "%{$this->currencySearch}%")
                        ->orWhere('symbol', 'like', "%{$this->currencySearch}%");
                });
            })
            ->orderBy('name')
            ->limit(20)
            ->get();

        if (blank($this->currencySearch) && filled($this->currencyId)) {
            $selected = Currency::query()
                ->withTrashed()
                ->whereKey($this->currencyId)
                ->whereNotIn('id', $currencies->pluck('id'))
                ->get();

            $currencies = $selected->merge($currencies);
        }

        return $currencies;
    }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = $column === 'created_at' ? 'desc' : 'asc';
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

    public function updatedMethod(): void
    {
        $this->resetPage();
    }

    public function updatedCurrencyId(): void
    {
        $this->resetPage();
    }

    public function updatedAmountOperator(): void
    {
        $this->amountValue = '';
        $this->amountMin = '';
        $this->amountMax = '';
        $this->resetPage();
    }

    public function updatedAmountValue(): void
    {
        $this->resetPage();
    }

    public function updatedAmountMin(): void
    {
        $this->resetPage();
    }

    public function updatedAmountMax(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public function updatedDateRange(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset([
            'search',
            'status',
            'method',
            'currencyId',
            'currencySearch',
            'amountOperator',
            'amountValue',
            'amountMin',
            'amountMax',
            'dateFrom',
            'dateTo',
            'dateRange',
        ]);

        $this->resetPage();
    }

    #[On('panels.administrator.finance-management.deposit.index.refresh')]
    public function refresh(): void
    {
        unset($this->deposits);
        unset($this->currencies);
    }
};
?>

<div>
    @php
        $isFa = app()->getLocale() === 'fa';
        $depositMethods = \App\Support\PaymentGateways::depositMethodOptions();
    @endphp

    <x-slot name="title">{{ __('general.deposits') }} - {{ config('app.name') }}</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('panels.administrator.dashboard.index') }}" icon="home" wire:navigate />
                <flux:breadcrumbs.item>{{ __('general.finance_management') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ __('general.deposits') }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>

            <flux:button class="shrink-0" variant="primary" color="teal" icon="plus" wire:click="$dispatch('panels.administrator.finance-management.deposit.create.assign-data')">
                {{ __('actions.create') }} {{ __('general.deposit') }}
            </flux:button>
        </div>

        <flux:card class="space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <flux:heading size="sm">{{ __('general.filters') }}</flux:heading>
                <flux:button size="sm" variant="ghost" icon="funnel" icon:variant="outline" wire:click="clearFilters">
                    {{ __('general.clear_filters') }}
                </flux:button>
            </div>

            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                <flux:input wire:model.live.debounce.300ms="search" icon="search" placeholder="{{ __('general.search') }}..." clearable />

                <flux:select wire:model.live="status" variant="listbox" searchable placeholder="{{ __('general.status') }}..." clearable>
                    @foreach (DepositStatusEnum::cases() as $statusOption)
                        <flux:select.option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model.live="method" variant="listbox" searchable placeholder="{{ __('general.method') }}..." clearable>
                    @foreach ($depositMethods as $methodKey => $methodLabelKey)
                        <flux:select.option value="{{ $methodKey }}">{{ \App\Support\PaymentGateways::methodLabel($methodKey) }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model.live="currencyId" variant="combobox" :filter="false" clearable placeholder="{{ __('general.currency') }}...">
                    <x-slot name="input">
                        <flux:select.input wire:model.live.debounce.300ms="currencySearch" placeholder="{{ __('general.currency') }}..." />
                    </x-slot>

                    @foreach ($this->currencies as $currency)
                        <flux:select.option value="{{ $currency->id }}" wire:key="deposit-currency-{{ $currency->id }}">
                            <span dir="ltr">{{ $currency->symbol }}</span> — {{ $currency->trashed() ? __('general.deleted') : $currency->name }}
                        </flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model.live="amountOperator" variant="listbox" searchable placeholder="{{ __('general.amount_filter') }}..." clearable>
                    <flux:select.option value="gt">{{ __('general.amount_greater_than') }}</flux:select.option>
                    <flux:select.option value="lt">{{ __('general.amount_less_than') }}</flux:select.option>
                    <flux:select.option value="between">{{ __('general.amount_between') }}</flux:select.option>
                </flux:select>
            </div>

            @if ($amountOperator === 'gt' || $amountOperator === 'lt')
                <div class="max-w-sm">
                    <flux:input
                        wire:model.live.debounce.300ms="amountValue"
                        type="number"
                        min="0"
                        step="0.00000001"
                        label="{{ __('general.amount') }}"
                        placeholder="100"
                        dir="ltr"
                        clearable
                    />
                </div>
            @elseif ($amountOperator === 'between')
                <div class="grid max-w-xl gap-3 md:grid-cols-2">
                    <flux:input
                        wire:model.live.debounce.300ms="amountMin"
                        type="number"
                        min="0"
                        step="0.00000001"
                        label="{{ __('general.amount_from') }}"
                        placeholder="100"
                        dir="ltr"
                        clearable
                    />
                    <flux:input
                        wire:model.live.debounce.300ms="amountMax"
                        type="number"
                        min="0"
                        step="0.00000001"
                        label="{{ __('general.amount_to') }}"
                        placeholder="900"
                        dir="ltr"
                        clearable
                    />
                </div>
            @endif

            <div class="grid gap-3 md:grid-cols-2">
                @if ($isFa)
                    <x-persian-date-picker
                        wire:model.live="dateFrom"
                        label="{{ __('general.date_from') }}"
                        placeholder="{{ __('general.date_from') }}"
                    />
                    <x-persian-date-picker
                        wire:model.live="dateTo"
                        label="{{ __('general.date_to') }}"
                        placeholder="{{ __('general.date_to') }}"
                    />
                @else
                    <div class="max-w-xl md:col-span-2">
                        <flux:date-picker
                            mode="range"
                            type="input"
                            wire:model.live="dateRange"
                            with-presets
                            clearable
                            label="{{ __('general.date_range') }}"
                            placeholder="{{ __('general.date_range') }}"
                        />
                    </div>
                @endif
            </div>

            <flux:table :paginate="$this->deposits">
                <flux:table.columns>
                    <flux:table.column>{{ __('general.user') }}</flux:table.column>
                    <flux:table.column>{{ __('general.currency') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'amount'" :direction="$sortDirection" wire:click="sort('amount')">{{ __('general.amount') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'fee'" :direction="$sortDirection" wire:click="sort('fee')">{{ __('general.fee') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'amount_settled'" :direction="$sortDirection" wire:click="sort('amount_settled')">{{ __('general.amount_settled') }}</flux:table.column>
                    <flux:table.column>{{ __('general.method') }}</flux:table.column>
                    <flux:table.column>{{ __('general.tracking_code') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'status'" :direction="$sortDirection" wire:click="sort('status')">{{ __('general.status') }}</flux:table.column>
                    <flux:table.column>{{ __('general.transactions') }}</flux:table.column>
                    <flux:table.column>{{ __('general.creator') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')">{{ __('general.created_at') }}</flux:table.column>
                    <flux:table.column align="end">{{ __('general.actions') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($this->deposits as $deposit)
                        @php
                            $user = $deposit->user;
                            $currency = $deposit->wallet?->currency;
                            $decimals = $currency?->decimals ?? 8;
                            $userLabel = ($user && ! $user->trashed()) ? $user->full_name : __('general.deleted');
                            $currencyLabel = ($currency && ! $currency->trashed()) ? $currency->name : __('general.deleted');
                            $currencySymbol = $currency?->symbol ?? __('general.deleted');
                            $methodLabel = \App\Support\PaymentGateways::methodLabel((string) $deposit->method);
                        @endphp
                        <flux:table.row :key="$deposit->id">
                            <flux:table.cell>
                                <div class="space-y-0.5">
                                    <div class="font-medium">{{ $userLabel }}</div>
                                    @if ($user && ! $user->trashed())
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400" dir="ltr">
                                            {{ $user->username }}
                                        </div>
                                    @endif
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    @if ($currency?->logo && ! $currency->trashed())
                                        <img src="{{ asset('storage/' . $currency->logo) }}" alt="{{ $currencyLabel }}" class="size-6 rounded object-contain" />
                                    @else
                                        <flux:icon.circle-dollar-sign variant="outline" class="size-4 text-teal-500" />
                                    @endif
                                    <div>
                                        <div class="font-medium" dir="ltr">{{ $currencySymbol }}</div>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $currencyLabel }}</div>
                                    </div>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell variant="strong">
                                <span dir="ltr" class="text-green-600 dark:text-green-400">
                                    {{ number_format((float) $deposit->amount, $decimals) }}
                                </span>
                            </flux:table.cell>
                            <flux:table.cell>
                                <span dir="ltr">{{ number_format((float) $deposit->fee, $decimals) }}</span>
                            </flux:table.cell>
                            <flux:table.cell>
                                <span dir="ltr">{{ number_format((float) $deposit->amount_settled, $decimals) }}</span>
                            </flux:table.cell>
                            <flux:table.cell>{{ $methodLabel }}</flux:table.cell>
                            <flux:table.cell>
                                <span dir="ltr">{{ $deposit->tracking_code ?: '—' }}</span>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="{{ $deposit->status->color() }}">
                                    {{ $deposit->status->label() }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="{{ $deposit->transactions_count > 0 ? 'teal' : 'zinc' }}">
                                    {{ $deposit->transactions_count }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                {{ $deposit->creator?->full_name ?? '—' }}
                            </flux:table.cell>
                            <flux:table.cell>{{ $deposit->created_at->toDynamicFormat('Y/m/d H:i:s') }}</flux:table.cell>
                            <flux:table.cell align="end">
                                <div class="flex justify-end gap-2">
                                    <flux:tooltip content="{{ __('general.edit') }}">
                                        <flux:button size="xs" variant="primary" color="blue" icon="pencil" icon:variant="outline" wire:click="$dispatch('panels.administrator.finance-management.deposit.edit.assign-data', { deposit: {{ $deposit->id }} })" />
                                    </flux:tooltip>
                                    <flux:tooltip content="{{ __('general.delete') }}">
                                        <flux:button size="xs" variant="danger" icon="trash" icon:variant="outline" wire:click="$dispatch('panels.administrator.finance-management.deposit.delete.assign-data', { deposit: {{ $deposit->id }} })" />
                                    </flux:tooltip>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="12">
                                <div class="flex flex-col items-center justify-center gap-2 py-10 text-center">
                                    <flux:icon.arrow-down-to-line variant="outline" class="size-8 text-zinc-400" />
                                    <flux:text>{{ __('general.no_results_found') }}</flux:text>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>

    <livewire:finance-management.deposit.create :key="'finance-deposit-create'" />
    <livewire:finance-management.deposit.edit :key="'finance-deposit-edit'" />
    <livewire:finance-management.deposit.delete :key="'finance-deposit-delete'" />
</div>
