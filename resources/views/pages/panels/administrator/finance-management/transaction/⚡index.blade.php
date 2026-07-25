<?php

use App\Models\Finance\Currency;
use App\Models\Finance\Deposit;
use App\Models\Finance\Transaction;
use App\Models\Finance\Wallet;
use App\Models\Finance\Withdrawal;
use App\Models\User;
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

    public string $type = '';

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
    public function transactions(): LengthAwarePaginator
    {
        $allowedSorts = ['amount', 'balance_after', 'created_at', 'type'];

        $sortBy = in_array($this->sortBy, $allowedSorts, true) ? $this->sortBy : 'created_at';
        $sortDirection = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        return Transaction::query()
            ->with([
                'wallet.user' => fn ($query) => $query->withTrashed(),
                'wallet.currency' => fn ($query) => $query->withTrashed(),
                'reference',
                'creator',
            ])
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('description', 'like', "%{$this->search}%")
                        ->orWhereHas('wallet.user', function ($query) {
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
            ->when($this->type, fn ($query) => $query->where('type', $this->type))
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

    public function updatedType(): void
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
            'type',
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

    #[On('panels.administrator.finance-management.transaction.index.refresh')]
    public function refresh(): void
    {
        unset($this->transactions);
        unset($this->currencies);
    }
};
?>

<div>
    @php
        $isFa = app()->getLocale() === 'fa';
    @endphp

    <x-slot name="title">{{ __('general.transactions') }} - {{ config('app.name') }}</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('panels.administrator.dashboard.index') }}" icon="home" wire:navigate />
                <flux:breadcrumbs.item>{{ __('general.finance_management') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ __('general.transactions') }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
        </div>

        <flux:card class="space-y-4">
            @php
                $activeFilters = collect([
                    $type,
                    $currencyId,
                    $amountOperator,
                    $dateFrom,
                    $dateTo,
                    $dateRange,
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
                        <flux:select wire:model.live="type" variant="listbox" searchable placeholder="{{ __('general.type') }}..." clearable>
                            <flux:select.option value="credit">{{ __('general.transaction_type_credit') }}</flux:select.option>
                            <flux:select.option value="debit">{{ __('general.transaction_type_debit') }}</flux:select.option>
                        </flux:select>

                        <flux:select wire:model.live="currencyId" variant="combobox" :filter="false" clearable placeholder="{{ __('general.currency') }}...">
                            <x-slot name="input">
                                <flux:select.input wire:model.live.debounce.300ms="currencySearch" placeholder="{{ __('general.currency') }}..." />
                            </x-slot>

                            @foreach ($this->currencies as $currency)
                                <flux:select.option value="{{ $currency->id }}" wire:key="transaction-currency-{{ $currency->id }}">
                                    <span dir="ltr">{{ $currency->symbol }}</span> — {{ $currency->trashed() ? __('general.deleted') : $currency->name }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>

                        <flux:select wire:model.live="amountOperator" variant="listbox" searchable placeholder="{{ __('general.amount_filter') }}..." clearable>
                            <flux:select.option value="gt">{{ __('general.amount_greater_than') }}</flux:select.option>
                            <flux:select.option value="lt">{{ __('general.amount_less_than') }}</flux:select.option>
                            <flux:select.option value="between">{{ __('general.amount_between') }}</flux:select.option>
                        </flux:select>

                        @if ($amountOperator === 'gt' || $amountOperator === 'lt')
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
                        @elseif ($amountOperator === 'between')
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
                        @endif

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
                            <flux:date-picker
                                mode="range"
                                type="input"
                                wire:model.live="dateRange"
                                with-presets
                                clearable
                                label="{{ __('general.date_range') }}"
                                placeholder="{{ __('general.date_range') }}"
                            />
                        @endif

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

            <flux:table :paginate="$this->transactions">
                <flux:table.columns>
                    <flux:table.column>{{ __('general.user') }}</flux:table.column>
                    <flux:table.column>{{ __('general.currency') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'type'" :direction="$sortDirection" wire:click="sort('type')">{{ __('general.type') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'amount'" :direction="$sortDirection" wire:click="sort('amount')">{{ __('general.amount') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'balance_after'" :direction="$sortDirection" wire:click="sort('balance_after')">{{ __('general.balance_after') }}</flux:table.column>
                    <flux:table.column>{{ __('general.description') }}</flux:table.column>
                    <flux:table.column>{{ __('general.reference') }}</flux:table.column>
                    <flux:table.column>{{ __('general.creator') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')">{{ __('general.created_at') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($this->transactions as $transaction)
                        @php
                            $wallet = $transaction->wallet;
                            $user = $wallet?->user;
                            $currency = $wallet?->currency;
                            $decimals = $currency?->decimals ?? 8;
                            $userLabel = ($user && ! $user->trashed()) ? $user->full_name : __('general.deleted');
                            $currencyLabel = ($currency && ! $currency->trashed()) ? $currency->name : __('general.deleted');
                            $currencySymbol = $currency?->symbol ?? __('general.deleted');
                        @endphp
                        <flux:table.row :key="$transaction->id">
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
                            <flux:table.cell>
                                <flux:badge size="sm" color="{{ $transaction->isCredit() ? 'green' : 'red' }}">
                                    {{ __('general.transaction_type_'.$transaction->type) }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell variant="strong">
                                <span dir="ltr" class="{{ $transaction->isCredit() ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ $transaction->isCredit() ? '+' : '-' }}{{ number_format((float) $transaction->amount, $decimals) }}
                                </span>
                            </flux:table.cell>
                            <flux:table.cell>
                                <span dir="ltr">
                                    {{ $transaction->balance_after !== null ? number_format((float) $transaction->balance_after, $decimals) : '—' }}
                                </span>
                            </flux:table.cell>
                            <flux:table.cell>
                                {{ $transaction->description ?: '—' }}
                            </flux:table.cell>
                            <flux:table.cell>
                                @if ($transaction->reference instanceof User)
                                    {{ $transaction->reference->full_name }}
                                @elseif ($transaction->reference instanceof Wallet)
                                    #{{ $transaction->reference->id }}
                                @elseif ($transaction->reference instanceof Currency)
                                    <span dir="ltr">{{ $transaction->reference->symbol }}</span>
                                @elseif ($transaction->reference instanceof Deposit)
                                    {{ __('general.deposit') }} #{{ $transaction->reference->id }}
                                @elseif ($transaction->reference instanceof Withdrawal)
                                    {{ __('general.withdrawal') }} #{{ $transaction->reference->id }}
                                @elseif ($transaction->reference)
                                    {{ class_basename($transaction->reference_type) }} #{{ $transaction->reference_id }}
                                @else
                                    —
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                {{ $transaction->creator?->full_name ?? '—' }}
                            </flux:table.cell>
                            <flux:table.cell>{{ $transaction->created_at->toDynamicFormat('Y/m/d H:i:s') }}</flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="9">
                                <div class="flex flex-col items-center justify-center gap-2 py-10 text-center">
                                    <flux:icon.arrow-left-right variant="outline" class="size-8 text-zinc-400" />
                                    <flux:text>{{ __('general.no_results_found') }}</flux:text>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>
</div>
