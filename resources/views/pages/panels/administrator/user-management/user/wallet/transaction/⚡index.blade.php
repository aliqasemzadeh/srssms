<?php

use App\Models\Finance\Transaction;
use App\Models\Finance\Wallet;
use App\Models\User;
use Flux\DateRange;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public User $user;

    public Wallet $wallet;

    public string $search = '';

    public string $type = '';

    public string $amountOperator = '';

    public string $amountValue = '';

    public string $amountMin = '';

    public string $amountMax = '';

    public ?string $dateFrom = null;

    public ?string $dateTo = null;

    public ?DateRange $dateRange = null;

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    public function mount(User $user, Wallet $wallet): void
    {
        abort_unless($wallet->user_id === $user->id, 404);

        $this->user = $user;
        $this->wallet = $wallet->load([
            'currency' => fn ($query) => $query->withTrashed(),
        ]);
    }

    #[Computed]
    public function transactions(): LengthAwarePaginator
    {
        $allowedSorts = ['amount', 'balance_after', 'created_at', 'type'];

        $sortBy = in_array($this->sortBy, $allowedSorts, true) ? $this->sortBy : 'created_at';
        $sortDirection = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        return Transaction::query()
            ->where('wallet_id', $this->wallet->id)
            ->with(['reference', 'creator'])
            ->when($this->search, function ($query) {
                $query->where('description', 'like', "%{$this->search}%");
            })
            ->when($this->type, fn ($query) => $query->where('type', $this->type))
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
            'type',
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

    #[On('panels.administrator.user-management.user.wallet.transaction.index.refresh')]
    public function refresh(): void
    {
        $this->wallet->refresh()->load([
            'currency' => fn ($query) => $query->withTrashed(),
        ]);

        unset($this->transactions);
    }
};
?>

<div>
    @php
        $currency = $wallet->currency;
        $currencyLabel = ($currency && ! $currency->trashed()) ? $currency->name : __('general.deleted');
        $currencySymbol = $currency?->symbol ?? __('general.deleted');
        $decimals = $currency?->decimals ?? 8;
        $step = $decimals > 0 ? '0.'.str_repeat('0', $decimals - 1).'1' : '1';
        $isFa = app()->getLocale() === 'fa';
    @endphp

    <x-slot name="title">{{ __('general.transactions') }} - {{ $currencySymbol }} - {{ $user->full_name }} - {{ config('app.name') }}</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('panels.administrator.dashboard.index') }}" icon="home" wire:navigate />
                <flux:breadcrumbs.item>{{ __('general.user_management') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('panels.administrator.user-management.user.index') }}" wire:navigate>{{ __('general.users') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('panels.administrator.user-management.user.wallet.index', $user) }}" wire:navigate>{{ $user->full_name }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('panels.administrator.user-management.user.wallet.index', $user) }}" wire:navigate>{{ __('general.wallets') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>
                    <span dir="ltr">{{ $currencySymbol }}</span>
                </flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ __('general.transactions') }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>

            <flux:button class="shrink-0" variant="primary" color="teal" icon="plus" wire:click="$dispatch('panels.administrator.user-management.user.wallet.transaction.create.assign-data')">
                {{ __('actions.create') }} {{ __('general.transaction') }}
            </flux:button>
        </div>

        <div class="grid gap-4 md:grid-cols-4">
            <flux:card class="space-y-1">
                <flux:text class="text-sm text-zinc-500">{{ __('general.currency') }}</flux:text>
                <div class="flex items-center gap-2">
                    @if ($currency?->logo && ! $currency->trashed())
                        <img src="{{ asset('storage/' . $currency->logo) }}" alt="{{ $currencyLabel }}" class="size-6 rounded object-contain" />
                    @else
                        <flux:icon.wallet variant="outline" class="size-4 text-teal-500" />
                    @endif
                    <flux:heading size="md">
                        <span dir="ltr">{{ $currencySymbol }}</span>
                        — {{ $currencyLabel }}
                    </flux:heading>
                </div>
            </flux:card>

            <flux:card class="space-y-1">
                <flux:text class="text-sm text-zinc-500">{{ __('general.balance') }}</flux:text>
                <flux:heading size="md" dir="ltr">{{ number_format((float) $wallet->balance, $decimals) }}</flux:heading>
            </flux:card>

            <flux:card class="space-y-1">
                <flux:text class="text-sm text-zinc-500">{{ __('general.locked_balance') }}</flux:text>
                <flux:heading size="md" dir="ltr">{{ number_format((float) $wallet->locked_balance, $decimals) }}</flux:heading>
            </flux:card>

            <flux:card class="space-y-1">
                <flux:text class="text-sm text-zinc-500">{{ __('general.available_balance') }}</flux:text>
                <flux:heading size="md" dir="ltr" class="text-teal-600 dark:text-teal-400">{{ number_format((float) $wallet->available_balance, $decimals) }}</flux:heading>
            </flux:card>
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

                <flux:select wire:model.live="type" variant="listbox" searchable placeholder="{{ __('general.type') }}..." clearable>
                    <flux:select.option value="credit">{{ __('general.transaction_type_credit') }}</flux:select.option>
                    <flux:select.option value="debit">{{ __('general.transaction_type_debit') }}</flux:select.option>
                </flux:select>

                <flux:select wire:model.live="amountOperator" variant="listbox" searchable placeholder="{{ __('general.amount_filter') }}..." clearable>
                    <flux:select.option value="gt">{{ __('general.amount_greater_than') }}</flux:select.option>
                    <flux:select.option value="lt">{{ __('general.amount_less_than') }}</flux:select.option>
                    <flux:select.option value="between">{{ __('general.amount_between') }}</flux:select.option>
                </flux:select>
            </div>

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
                    <div class="md:col-span-2 max-w-xl">
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

            @if ($amountOperator === 'gt' || $amountOperator === 'lt')
                <div class="max-w-sm">
                    <flux:input
                        wire:model.live.debounce.300ms="amountValue"
                        type="number"
                        min="0"
                        step="{{ $step }}"
                        label="{{ __('general.amount') }}"
                        placeholder="{{ $amountOperator === 'gt' ? '100' : '100' }}"
                        dir="ltr"
                        clearable
                    />
                </div>
            @elseif ($amountOperator === 'between')
                <div class="grid gap-3 max-w-xl md:grid-cols-2">
                    <flux:input
                        wire:model.live.debounce.300ms="amountMin"
                        type="number"
                        min="0"
                        step="{{ $step }}"
                        label="{{ __('general.amount_from') }}"
                        placeholder="100"
                        dir="ltr"
                        clearable
                    />
                    <flux:input
                        wire:model.live.debounce.300ms="amountMax"
                        type="number"
                        min="0"
                        step="{{ $step }}"
                        label="{{ __('general.amount_to') }}"
                        placeholder="900"
                        dir="ltr"
                        clearable
                    />
                </div>
            @endif

            <flux:table :paginate="$this->transactions">
                <flux:table.columns>
                    <flux:table.column sortable :sorted="$sortBy === 'type'" :direction="$sortDirection" wire:click="sort('type')">{{ __('general.type') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'amount'" :direction="$sortDirection" wire:click="sort('amount')">{{ __('general.amount') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'balance_after'" :direction="$sortDirection" wire:click="sort('balance_after')">{{ __('general.balance_after') }}</flux:table.column>
                    <flux:table.column>{{ __('general.description') }}</flux:table.column>
                    <flux:table.column>{{ __('general.reference') }}</flux:table.column>
                    <flux:table.column>{{ __('general.creator') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')">{{ __('general.created_at') }}</flux:table.column>
                    <flux:table.column align="end">{{ __('general.actions') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($this->transactions as $transaction)
                        <flux:table.row :key="$transaction->id">
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
                                @if ($transaction->reference instanceof \App\Models\User)
                                    {{ $transaction->reference->full_name }}
                                @elseif ($transaction->reference instanceof \App\Models\Finance\Wallet)
                                    #{{ $transaction->reference->id }}
                                @elseif ($transaction->reference instanceof \App\Models\Finance\Currency)
                                    <span dir="ltr">{{ $transaction->reference->symbol }}</span>
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
                            <flux:table.cell align="end">
                                <div class="flex justify-end gap-2">
                                    <flux:tooltip content="{{ __('general.edit') }}">
                                        <flux:button size="xs" variant="primary" color="blue" icon="pencil" icon:variant="outline" wire:click="$dispatch('panels.administrator.user-management.user.wallet.transaction.edit.assign-data', { transaction: {{ $transaction->id }} })" />
                                    </flux:tooltip>
                                    <flux:tooltip content="{{ __('general.delete') }}">
                                        <flux:button size="xs" variant="danger" icon="trash" icon:variant="outline" wire:click="$dispatch('panels.administrator.user-management.user.wallet.transaction.delete.assign-data', { transaction: {{ $transaction->id }} })" />
                                    </flux:tooltip>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="8">
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

    <livewire:user-management.user.wallet.transaction.create :user="$user" :wallet="$wallet" :key="'transaction-create-'.$wallet->id" />
    <livewire:user-management.user.wallet.transaction.edit :user="$user" :wallet="$wallet" :key="'transaction-edit-'.$wallet->id" />
    <livewire:user-management.user.wallet.transaction.delete :user="$user" :wallet="$wallet" :key="'transaction-delete-'.$wallet->id" />
</div>
